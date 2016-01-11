<?php
namespace ParallelTask\Benchmark;

use ParallelTask\Queue\RabbitMQ\RabbitMQQueue;
use ParallelTask\Queue\Redis\PredisQueue;
use ParallelTask\Queue\Redis\RedisQueue;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Predis\Client;
use Redis;

class QueueFactory
{
    public function make($queueType)
    {
        switch ($queueType) {
            case 'redis':
                $redisConnection = new Redis();
                $redisConnection->pconnect('localhost');
                return new RedisQueue($redisConnection);
            case 'predis':
                $client = new Client();
                return new PredisQueue($client);
            case 'rabbitMQ':
                $amqpConnection = new AMQPStreamConnection('localhost', '5672', 'guest', 'guest');
                return new RabbitMQQueue($amqpConnection);
            default:
                throw new \InvalidArgumentException('Invalid queue type');
        }
    }
}
