<?php
namespace ParallelTask\Queue\RabbitMQ;

use ParallelTask\Queue\InputMessage;
use ParallelTask\Queue\InputMessageIdentifier;
use ParallelTask\Queue\OutputMessage;
use ParallelTask\Queue\Queue;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Ramsey\Uuid\Uuid;

class RabbitMQQueue implements Queue
{
    /** @var AMQPStreamConnection */
    private $connection;
    /** @var \AMQPChannel */
    private $channel;
    /** @var string */
    private $replyQueueName;
    /** @var OutputMessage[] */
    private $outputMessages;
    /** @var string */
    private $consumerTag;
    /** @var bool[] */
    private $declaredQueue;

    public function __construct(AMQPStreamConnection $connection)
    {
        $this->connection = $connection;
        $this->channel = $connection->channel();
    }

    public function putInput($type, InputMessage $inputMessage)
    {
        $this->declareQueue($type);

        $amqpMessage = new AMQPMessage($inputMessage->getData());
        $this->channel->basic_publish($amqpMessage, $this->getExchangeName($type));
    }

    public function submitInput($type, InputMessage $inputMessage)
    {
        $this->declareQueue($type);

        if (is_null($this->replyQueueName)) {
            list($this->replyQueueName, ,) = $this->channel->queue_declare('', false, false, true, false);
            $replyCallbackCapture = function (AMQPMessage $amqpMessage) {
                $outputMessage = new OutputMessage($amqpMessage->body);
                $id = $amqpMessage->get('correlation_id');
                $this->outputMessages[$id] = $outputMessage;
                $channel = $amqpMessage->delivery_info['channel'];
                $channel->basic_ack($amqpMessage->delivery_info['delivery_tag']);
            };

            $this->channel->basic_qos(null, 1, null);
            $this->channel->basic_consume($this->replyQueueName, '', false, false, true, false, $replyCallbackCapture);
        }

        $id = Uuid::uuid4()->toString();

        $amqpMessage = new AMQPMessage($inputMessage->getData(), ['reply_to' => $this->replyQueueName, 'correlation_id' => $id]);
        $this->channel->basic_publish($amqpMessage, $this->getExchangeName($type));

        return new InputMessageIdentifier($id);
    }

    public function run($type, callable $runCallback)
    {
        $runCallbackWrapper = function (AMQPMessage $amqpMessage) use ($runCallback) {
            $inputMessage = new InputMessage($amqpMessage->body);
            /** @var OutputMessage $outputMessage */
            $outputMessage = $runCallback($inputMessage);

            $channel = $amqpMessage->delivery_info['channel'];

            if ($amqpMessage->has('reply_to')) {
                $replyQueueName = $amqpMessage->get('reply_to');
                $id = $amqpMessage->get('correlation_id');
                $replyAmqpMessage = new AMQPMessage($outputMessage->getData(), ['correlation_id' => $id]);
                $channel->basic_publish($replyAmqpMessage, '', $replyQueueName);
            }

            $channel->basic_ack($amqpMessage->delivery_info['delivery_tag']);
        };

        if (is_null($this->consumerTag)) {
            $this->consumerTag = 'consumer_' . substr(Uuid::uuid4()->toString(), 0, 8);
            $this->channel->basic_qos(null, 1, null);
            $this->channel->basic_consume($this->getQueueName($type), $this->consumerTag, false, false, false, false, $runCallbackWrapper);
        }
        $this->channel->wait();
    }

    public function getOutput($type, InputMessageIdentifier $identifier)
    {
        $id = $identifier->getId();

        do {
            $this->channel->wait();
        } while (!isset($this->outputMessages[$id]));

        return $this->outputMessages[$id];
    }

    private function declareQueue($type)
    {
        if (isset($this->declaredQueue[$type])) {
            return;
        }
        $exchangeName = $this->getExchangeName($type);
        $queueName = $this->getQueueName($type);

        $this->channel->exchange_declare($exchangeName, 'direct');
        $this->channel->queue_declare($queueName, false, false, false, false);
        $this->channel->queue_bind($queueName, $exchangeName);
        $this->declaredQueue[$type] = true;
    }

    private function getExchangeName($type)
    {
        return $type . '_ex';
    }

    private function getQueueName($type)
    {
        return $type . '_q';
    }

    public function __destruct()
    {
        $this->channel->close();
        unset($this->channel);
        $this->connection->close();
        unset($this->connection);

        unset($this->replyQueueName);
        unset($this->consumerTag);
        unset($this->outputMessages);
    }
}
