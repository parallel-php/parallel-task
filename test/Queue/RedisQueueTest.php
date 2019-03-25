<?php
declare(strict_types=1);

namespace ParallelTask\Queue;

use ParallelTask\Queue\Redis\RedisQueue;

class RedisQueueTest extends QueueTest
{

    protected function getQueueInstance()
    {
        $redisConnection = new \Redis();
        $redisConnection->pconnect('localhost');

        return new RedisQueue($redisConnection);
    }
}
