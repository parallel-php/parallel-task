<?php

use ParallelTask\Benchmark\QueueFactory;
use ParallelTask\Task\TaskMessageTransformer;
use ParallelTask\Task\TaskRunner;
use ParallelTask\Worker;

require_once dirname(__DIR__) . '/../vendor/autoload.php';

$queueType = $argv[1];
$type = $argv[2];

$queueFactory = new QueueFactory();

$queue = $queueFactory->make($queueType);
$transformer = new TaskMessageTransformer();
$worker = new Worker(new TaskRunner($queue, $transformer, $transformer));

$worker->work($type);
