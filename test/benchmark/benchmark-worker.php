<?php

use ParallelTask\Benchmark\QueueFactory;
use ParallelTask\Task\TaskFactorySimple;
use ParallelTask\Task\TaskMessageSerializeTransformer;
use ParallelTask\Task\TaskRunner;
use ParallelTask\Worker;

require_once dirname(__DIR__) . '/../vendor/autoload.php';

$queueType = $argv[1];
$type = $argv[2];

$queueFactory = new QueueFactory();

$queue = $queueFactory->make($queueType);
$transformer = new TaskMessageSerializeTransformer();
$taskFactory = new TaskFactorySimple();
$supervisor = new \ParallelTask\Task\TaskRunnerNullSupervisor();
$worker = new Worker(new TaskRunner($queue, $transformer, $taskFactory, $transformer, $supervisor));

$worker->work($type);
