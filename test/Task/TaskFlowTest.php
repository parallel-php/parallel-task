<?php
use ParallelTask\Fixture\LocalArrayTestQueue;
use ParallelTask\Fixture\TestTask;
use ParallelTask\Task\TaskFactorySimple;
use ParallelTask\Task\TaskInput;
use ParallelTask\Task\TaskMessageSerializeTransformer;
use ParallelTask\Task\TaskRunner;
use ParallelTask\Task\TaskRunnerNullSupervisor;
use ParallelTask\Task\TaskScheduler;

class TaskFlowTest extends \PHPUnit\Framework\TestCase
{
    public function testFlowSuccess()
    {
        $queue = new LocalArrayTestQueue();
        $messageTransformer = new TaskMessageSerializeTransformer();
        $taskFactory = new TaskFactorySimple();
        $taskRunnerSupervisor = new TaskRunnerNullSupervisor();

        $taskScheduler = new TaskScheduler($queue, $messageTransformer, $messageTransformer);
        $taskRunner = new TaskRunner($queue, $messageTransformer, $taskFactory, $messageTransformer, $taskRunnerSupervisor);

        $input = [5, 6];
        $taskInput = new TaskInput($input);

        $futureTaskResult = $taskScheduler->submit('testType', TestTask::class, $taskInput);
        $taskRunner->runOnce('testType');
        $result = $futureTaskResult->getTaskResult()->getResult();

        $this->assertEquals($result, $input[0]);
    }

    public function testFlowException()
    {
        $queue = new LocalArrayTestQueue();
        $messageTransformer = new TaskMessageSerializeTransformer();
        $taskFactory = new TaskFactorySimple();
        $taskRunnerSupervisor = new TaskRunnerNullSupervisor();

        $taskScheduler = new TaskScheduler($queue, $messageTransformer, $messageTransformer);
        $taskRunner = new TaskRunner($queue, $messageTransformer, $taskFactory, $messageTransformer, $taskRunnerSupervisor);

        $input = [];
        $taskInput = new TaskInput($input);

        $futureTaskResult = $taskScheduler->submit('testType', TestTask::class, $taskInput);
        $taskRunner->runOnce('testType');
        $this->expectException(\Exception::class);
        $futureTaskResult->getTaskResult()->getResult();
    }
}
