<?php
namespace ParallelTask\Queue\Redis;

use ParallelTask\Queue\InputMessage;
use ParallelTask\Queue\InputMessageIdentifier;
use ParallelTask\Queue\NonCallbackQueue;
use ParallelTask\Queue\OutputMessage;
use ParallelTask\Queue\InputMessageWithIdentifier;
use Ramsey\Uuid\Uuid;

class RedisQueue extends NonCallbackQueue
{
    /**
     * @var int
     */
    private $messageTimeout = 60;

    /**
     * @var \Redis
     */
    private $redis;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }


    /**
     * @param $type
     * @param InputMessage $inputMessage
     */
    public function putInput($type, InputMessage $inputMessage)
    {
        $id = Uuid::uuid4()->toString();
        $data = $inputMessage->getData();

        $this->storeInput($type, $id, $data, false);
    }

    /**
     * @param $type
     * @param InputMessage $inputMessage
     * @return InputMessageIdentifier
     */
    public function submitInput($type, InputMessage $inputMessage)
    {
        $id = Uuid::uuid4()->toString();
        $data = $inputMessage->getData();

        $this->storeInput($type, $id, $data, true);

        return new InputMessageIdentifier($id);
    }

    private function storeInput($type, $id, $data, $captureResult)
    {
        $this->redis->multi();
        $this->redis->hSet($this->getMessageKey($type), $id, $data);
        $this->redis->lPush($this->getMessageQueueKey($type), $id);
        $this->redis->hSet($this->getMessageQueueTimeKey($type), $id, time());
        if ($captureResult) {
            $this->redis->hSet($this->getMessageResultKey($type), $id, 'MessageNotFinished');
        }
        $this->redis->exec();
    }

    /**
     * @param string $type
     * @return InputMessageWithIdentifier
     */
    public function getInput($type)
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
        $this->redis->hSet($this->getMessageStartTimeKey($type), $messageId, time());

        $data = $this->redis->hGet($this->getMessageKey($type), $messageId);
        $inputMessage = new InputMessage($data);
        $identifier = new InputMessageIdentifier($messageId);

        return new InputMessageWithIdentifier($identifier, $inputMessage);
    }

    /**
     * @param string $type
     * @param InputMessageIdentifier $identifier
     * @param OutputMessage $outputMessage
     */
    public function putOutput($type, InputMessageIdentifier $identifier, OutputMessage $outputMessage)
    {
        $messageId = $identifier->getId();

        $captureResult = 'MessageNotFinished' === $this->redis->hGet($this->getMessageResultKey($type), $messageId);

        $this->redis->multi();
        $this->redis->lRem($this->getMessageRunKey($type), $messageId, 0);
        $this->redis->hDel($this->getMessageStartTimeKey($type), $messageId);
        $this->redis->lRem($this->getMessageQueueKey($type), $messageId, 0);
        $this->redis->hDel($this->getMessageQueueTimeKey($type), $messageId);
        $this->redis->hDel($this->getMessageKey($type), $messageId);
        if ($captureResult) {
            $data = $outputMessage->getData();
            $this->redis->hSet($this->getMessageResultKey($type), $messageId, $data);
            $this->redis->lPush($this->getMessageResultReadyKey($type, $messageId), 'true');
        }
        $this->redis->exec();

    }

    /**
     * @param string $type
     * @param InputMessageIdentifier $identifier
     * @return OutputMessage
     */
    public function getOutput($type, InputMessageIdentifier $identifier)
    {
        $messageId = $identifier->getId();

        $data = $this->redis->hGet($this->getMessageResultKey($type), $messageId);
        if (empty($data)) {
            return null;
        }

        do {
            $resultReady = $this->redis->blPop($this->getMessageResultReadyKey($type, $messageId), 1);
        } while (empty($resultReady));

        $data = $this->redis->hGet($this->getMessageResultKey($type), $messageId);
        $this->redis->hDel($this->getMessageResultKey($type), $messageId);

        return new OutputMessage($data);
    }

    private function requeueOldWorkingMessages($type)
    {
        $messageIds = array_unique($this->redis->lRange($this->getMessageRunKey($type), 0, -1));
        foreach ($messageIds as $messageId) {
            $time = $this->redis->hGet($this->getMessageStartTimeKey($type), $messageId);
            if (!empty($time) && time() > $this->messageTimeout + (int)$time) {
                $this->redis->multi();
                $this->redis->rPush($this->getMessageQueueKey($type), $messageId);
                $this->redis->lRem($this->getMessageRunKey($type), $messageId, 1);
                $this->redis->hDel($this->getMessageStartTimeKey($type), $messageId);
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


}
