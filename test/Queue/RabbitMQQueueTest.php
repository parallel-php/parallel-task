<?php
namespace ParallelTask\Queue;

use ParallelTask\Queue\RabbitMQ\RabbitMQQueue;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQQueueTest extends QueueTest
{

    protected function getQueueInstance()
    {
        $amqpConnection = new AMQPStreamConnection('localhost', '5672', 'guest', 'guest');

        return new RabbitMQQueue($amqpConnection);
    }
}
