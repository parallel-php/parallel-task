<?php
namespace ParallelTask\Queue;

use ParallelTask\Fixture\LocalArrayTestQueue;

class LocalArrayTestQueueTest extends QueueTest
{

    protected function getQueueInstance()
    {
        return new LocalArrayTestQueue();
    }
}
