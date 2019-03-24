<?php
declare(strict_types=1);

namespace ParallelTask\Queue\Redis;

use ParallelTask\Queue\InputMessage;
use ParallelTask\Queue\InputMessageIdentifier;
use ParallelTask\Queue\InputMessageWithIdentifier;
use ParallelTask\Queue\NonCallbackConsumeQueue;
use ParallelTask\Queue\OutputMessage;
use ParallelTask\Queue\PublishQueue;
use ParallelTask\Queue\Queue;
use Predis\Client;
use Predis\ClientContextInterface;
use Ramsey\Uuid\Uuid;

class PredisQueue extends NonCallbackConsumeQueue implements PublishQueue, Queue
{
    /**
     * @var int
     */
    private $messageTimeout = 60;

    /**
     * @var Client
     */
    private $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function putInput(string $type, InputMessage $inputMessage): void
    {
        $id = Uuid::uuid4()->toString();
        $data = $inputMessage->getData();

        $this->storeInput($type, $id, $data, false);
    }

    public function submitInput(string $type, InputMessage $inputMessage): InputMessageIdentifier
    {
        $id = Uuid::uuid4()->toString();
        $data = $inputMessage->getData();

        $this->storeInput($type, $id, $data, true);

        return new InputMessageIdentifier($id);
    }

    private function storeInput($type, $id, $data, $captureResult)
    {
        $this->redis->pipeline(['atomic' => true], function (ClientContextInterface $redis) use ($type, $id, $data, $captureResult) {
            $redis->hset($this->getMessageKey($type), $id, $data);
            $redis->lpush($this->getMessageQueueKey($type), $id);
            $redis->hset($this->getMessageQueueTimeKey($type), $id, time());
            if ($captureResult) {
                $redis->hset($this->getMessageResultKey($type), $id, 'MessageNotFinished');
            }
        });
    }

    public function getInput(string $type): InputMessageWithIdentifier
    {
        if (rand(0, 99) == 0) {
            $this->requeueOldWorkingMessages($type);
        }

        do {
            $messageId = $this->redis->brpoplpush($this->getMessageQueueKey($type), $this->getMessageRunKey($type), 1);
            if (empty($messageId) && rand(0, 9) == 0) {
                $this->requeueOldWorkingMessages($type);
            }
        } while (empty($messageId));
        $this->redis->hset($this->getMessageStartTimeKey($type), $messageId, time());

        $data = $this->redis->hget($this->getMessageKey($type), $messageId);
        $inputMessage = new InputMessage($data);
        $identifier = new InputMessageIdentifier($messageId);

        return new InputMessageWithIdentifier($identifier, $inputMessage);
    }

    public function putOutput(string $type, InputMessageIdentifier $identifier, OutputMessage $outputMessage): void
    {
        $messageId = $identifier->getId();

        $captureResult = 'MessageNotFinished' === $this->redis->hget($this->getMessageResultKey($type), $messageId);

        $this->redis->pipeline(['atomic' => true], function (ClientContextInterface $redis) use ($type, $messageId, $outputMessage, $captureResult) {
            $redis->lrem($this->getMessageRunKey($type), 0, $messageId);
            $redis->hdel($this->getMessageStartTimeKey($type), $messageId);
            $redis->lrem($this->getMessageQueueKey($type), 0, $messageId);
            $redis->hdel($this->getMessageQueueTimeKey($type), $messageId);
            $redis->hdel($this->getMessageKey($type), $messageId);
            if ($captureResult) {
                $data = $outputMessage->getData();
                $redis->hset($this->getMessageResultKey($type), $messageId, $data);
                $redis->lpush($this->getMessageResultReadyKey($type, $messageId), 'true');
            }
        });
    }

    public function getOutput(string $type, InputMessageIdentifier $identifier): OutputMessage
    {
        $messageId = $identifier->getId();

        $data = $this->redis->hget($this->getMessageResultKey($type), $messageId);
        if (empty($data)) {
            return null;
        }

        do {
            $resultReady = $this->redis->blpop($this->getMessageResultReadyKey($type, $messageId), 1);
        } while (empty($resultReady));

        $data = $this->redis->hget($this->getMessageResultKey($type), $messageId);
        $this->redis->hdel($this->getMessageResultKey($type), $messageId);

        return new OutputMessage($data);
    }

    private function requeueOldWorkingMessages($type)
    {
        $messageIds = array_unique($this->redis->lrange($this->getMessageRunKey($type), 0, -1));
        foreach ($messageIds as $messageId) {
            $time = $this->redis->hget($this->getMessageStartTimeKey($type), $messageId);
            if (!empty($time) && time() > $this->messageTimeout + (int)$time) {
                $this->redis->pipeline(['atomic' => true], function (ClientContextInterface $redis) use ($type, $messageId) {
                    $redis->rpush($this->getMessageQueueKey($type), $messageId);
                    $redis->lrem($this->getMessageRunKey($type), 1, $messageId);
                    $redis->hdel($this->getMessageStartTimeKey($type), $messageId);
                });
            }
        }
    }

    private function getMessageRunKey($type)
    {
        return 'thread-worker-run-' . $type;
    }

    private function getMessageStartTimeKey($type)
    {
        return 'thread-worker-start-time-' . $type;
    }

    private function getMessageResultReadyKey($type, $messageId)
    {
        return 'thread-worker-result-ready-' . $type . '-' . $messageId;
    }

    private function getMessageKey($type)
    {
        return 'thread-worker-message-' . $type;
    }

    private function getMessageQueueKey($type)
    {
        return 'thread-worker-queue-' . $type;
    }

    private function getMessageQueueTimeKey($type)
    {
        return 'thread-worker-queue-time-' . $type;
    }

    private function getMessageResultKey($type)
    {
        return 'thread-worker-result-' . $type;
    }

    public function __destruct()
    {
        $this->redis->disconnect();
        unset($this->redis);
    }
}
