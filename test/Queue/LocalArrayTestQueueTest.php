<?php
namespace ParallelTask\Queue;

use ParallelTask\Fixture\LocalArrayTestQueue;

class LocalArrayTestQueueTest extends QueueTest
{

    private $queue = null;

    protected function getQueueInstance()
    {
        if (!isset($this->queue)) {
            $this->queue = new LocalArrayTestQueue();
        }
        return $this->queue;
    }
}
