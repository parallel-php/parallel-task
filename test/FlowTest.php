<?php

use ParallelTask\Fixture\LocalArrayTestQueue;
use ParallelTask\Fixture\TestTask;
use ParallelTask\Task\TaskFactorySimple;
use ParallelTask\Task\TaskMessageSerializeTransformer;
use ParallelTask\Task\TaskRunner;
use ParallelTask\Task\TaskScheduler;

class FlowTest extends \PHPUnit_Framework_TestCase
{
    public function testFlowSuccess()
    {
        $queue = new LocalArrayTestQueue();
        $messageTransformer = new TaskMessageSerializeTransformer();
        $taskFactory = new TaskFactorySimple();

        $taskScheduler = new TaskScheduler($queue, $messageTransformer, $messageTransformer);
        $taskRunner = new TaskRunner($queue, $messageTransformer, $taskFactory, $messageTransformer);

        $executor = new \ParallelTask\Executor($taskScheduler);

        $parameters = [5, 6];

        $futureResult = $executor->submit('testType', TestTask::class, $parameters);
        $taskRunner->runOnce('testType');
        $result = $futureResult->getResult();

        $this->assertEquals($result, $parameters[0]);
    }

    public function testFlowException()
    {
        $queue = new LocalArrayTestQueue();
        $messageTransformer = new TaskMessageSerializeTransformer();
        $taskFactory = new TaskFactorySimple();

        $taskScheduler = new TaskScheduler($queue, $messageTransformer, $messageTransformer);
        $taskRunner = new TaskRunner($queue, $messageTransformer, $taskFactory, $messageTransformer);

        $executor = new \ParallelTask\Executor($taskScheduler);

        $parameters = [];

        $futureResult = $executor->submit('testType', TestTask::class, $parameters);
        $taskRunner->runOnce('testType');
        $this->setExpectedException(\Exception::class);
        $futureResult->getResult();
    }
}
