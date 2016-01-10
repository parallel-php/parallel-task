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

    /**
     * @var \AMQPChannel
     */
    private $channel;

    public function __construct(AMQPStreamConnection $connection)
    {
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

        list($replyQueueName, ,) = $this->channel->queue_declare('', false, false, true, false);

        $amqpMessage = new AMQPMessage($inputMessage->getData(), ['reply_to' => $replyQueueName]);
        $this->channel->basic_publish($amqpMessage, $this->getExchangeName($type));

        return new InputMessageIdentifier($replyQueueName);
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
                $replyAmqpMessage = new AMQPMessage($outputMessage->getData());
                $channel->basic_publish($replyAmqpMessage, '', $replyQueueName);
            }

            $channel->basic_ack($amqpMessage->delivery_info['delivery_tag']);
        };

        $consumerTag = 'consumer_' . substr(Uuid::uuid4()->toString(), 0, 8);
        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($this->getQueueName($type), $consumerTag, false, false, false, false, $runCallbackWrapper);
        $this->channel->wait();
        $this->channel->basic_cancel($consumerTag);

    }

    public function getOutput($type, InputMessageIdentifier $identifier)
    {
        $replyQueueName = $identifier->getId();

        $outputMessage = null;
        $outputCallbackCapture = function (AMQPMessage $amqpMessage) use (&$outputMessage) {
            $outputMessage = new OutputMessage($amqpMessage->body);
        };

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($replyQueueName, '', false, false, true, false, $outputCallbackCapture);
        $this->channel->wait();

        return $outputMessage;
    }

    private function declareQueue($type)
    {
        $exchangeName = $this->getExchangeName($type);
        $queueName = $this->getQueueName($type);

        $this->channel->exchange_declare($exchangeName, 'direct');
        $this->channel->queue_declare($queueName, false, false, false, false);
        $this->channel->queue_bind($queueName, $exchangeName);
    }

    private function getExchangeName($type)
    {
        return $type . '_ex';
    }

    private function getQueueName($type)
    {
        return $type . '_q';
    }
}
