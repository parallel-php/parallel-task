<?php
declare(strict_types=1);

namespace ParallelTask;

use ParallelTask\Queue\ConsumeQueue;
use ParallelTask\Queue\PublishQueue;
use ParallelTask\Task\TaskFactory;
use ParallelTask\Task\TaskFactorySimple;
use ParallelTask\Task\TaskInputMessageTransformer;
use ParallelTask\Task\TaskMessageSerializeTransformer;
use ParallelTask\Task\TaskResultMessageTransformer;
use ParallelTask\Task\TaskRunner;
use ParallelTask\Task\TaskRunnerNullSupervisor;
use ParallelTask\Task\TaskRunnerSupervisor;
use ParallelTask\Task\TaskScheduler;

class ExecutorWorkerBuilder
{
    /** @var PublishQueue */
    private $publishQueue;
    /** @var ConsumeQueue */
    private $consumeQueue;
    /** @var TaskInputMessageTransformer */
    private $taskInputMessageTransformer;
    /** @var TaskResultMessageTransformer */
    private $taskResultMessageTransformer;
    /** @var TaskFactory */
    private $taskFactory;
    /** @var TaskRunnerSupervisor */
    private $taskRunnerSupervisor;

    public function __construct()
    {
        $this->taskInputMessageTransformer = $this->taskResultMessageTransformer = new TaskMessageSerializeTransformer();
        $this->taskFactory = new TaskFactorySimple();
        $this->taskRunnerSupervisor = new TaskRunnerNullSupervisor();
    }

    public function withPublishQueue(PublishQueue $queue): ExecutorWorkerBuilder
    {
        $this->publishQueue = $queue;
        return $this;
    }

    public function withConsumeQueue(ConsumeQueue $queue): ExecutorWorkerBuilder
    {
        $this->consumeQueue = $queue;
        return $this;
    }

    public function withTaskInputMessageTransformer(TaskInputMessageTransformer $taskMessageTransformer): ExecutorWorkerBuilder
    {
        $this->taskInputMessageTransformer = $taskMessageTransformer;
        return $this;
    }

    public function withTaskResultMessageTransformer(TaskResultMessageTransformer $taskMessageTransformer): ExecutorWorkerBuilder
    {
        $this->taskResultMessageTransformer = $taskMessageTransformer;
        return $this;
    }

    public function withTaskFactory(TaskFactory $taskFactory): ExecutorWorkerBuilder
    {
        $this->taskFactory = $taskFactory;
        return $this;
    }

    public function withTaskRunnerSupervisor(TaskRunnerSupervisor $taskRunnerSupervisor): ExecutorWorkerBuilder
    {
        $this->taskRunnerSupervisor = $taskRunnerSupervisor;
        return $this;
    }

    public function buildExecutor(): Executor
    {
        if (!$this->publishQueue instanceof PublishQueue) {
            throw new \RuntimeException('No queue was set. Use withPublishQueue($queue) method to set one before building');
        }

        $taskScheduler = new TaskScheduler($this->publishQueue, $this->taskInputMessageTransformer, $this->taskResultMessageTransformer);

        return new Executor($taskScheduler);
    }

    public function buildWorker(): Worker
    {
        if (!$this->consumeQueue instanceof ConsumeQueue) {
            throw new \RuntimeException('No queue was set. Use withConsumeQueue($queue) method to set one before building');
        }

        $taskRunner = new TaskRunner($this->consumeQueue, $this->taskInputMessageTransformer, $this->taskFactory, $this->taskResultMessageTransformer, $this->taskRunnerSupervisor);

        return new Worker($taskRunner);
    }
}
