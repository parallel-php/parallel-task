<?php
declare(strict_types=1);

namespace ParallelTask\Queue;

use ParallelTask\Queue\Redis\PredisQueue;
use Predis\Client;

class PredisQueueTest extends QueueTest
{

    protected function getQueueInstance()
    {
        $client = new Client();

        return new PredisQueue($client);
    }
}
