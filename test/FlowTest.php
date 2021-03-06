<?php
declare(strict_types=1);

use ParallelTask\Fixture\LocalArrayTestQueue;
use ParallelTask\Fixture\TestTask;
use ParallelTask\Task\TaskFactorySimple;
use ParallelTask\Task\TaskMessageSerializeTransformer;
use ParallelTask\Task\TaskRunner;
use ParallelTask\Task\TaskRunnerNullSupervisor;
use ParallelTask\Task\TaskScheduler;

class FlowTest extends \PHPUnit\Framework\TestCase
{
    public function testFlowSuccess()
    {
        $queue = new LocalArrayTestQueue();
        $messageTransformer = new TaskMessageSerializeTransformer();
        $taskFactory = new TaskFactorySimple();
        $taskRunnerSupervisor = new TaskRunnerNullSupervisor();

        $taskScheduler = new TaskScheduler($queue, $messageTransformer, $messageTransformer);
        $taskRunner = new TaskRunner($queue, $messageTransformer, $taskFactory, $messageTransformer, $taskRunnerSupervisor);

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
        $taskRunnerSupervisor = new TaskRunnerNullSupervisor();

        $taskScheduler = new TaskScheduler($queue, $messageTransformer, $messageTransformer);
        $taskRunner = new TaskRunner($queue, $messageTransformer, $taskFactory, $messageTransformer, $taskRunnerSupervisor);

        $executor = new \ParallelTask\Executor($taskScheduler);

        $parameters = [];

        $futureResult = $executor->submit('testType', TestTask::class, $parameters);
        $taskRunner->runOnce('testType');
        $this->expectException(\RuntimeException::class);
        $futureResult->getResult();
    }
}
