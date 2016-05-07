<?php
namespace ParallelTask;

use ParallelTask\Queue\Queue;
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
    /** @var Queue */
    private $queue;
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

    /**
     * @param Queue $queue
     * @return ExecutorWorkerBuilder
     */
    public function withQueue(Queue $queue)
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * @param TaskInputMessageTransformer $taskMessageTransformer
     * @return ExecutorWorkerBuilder
     */
    public function withTaskInputMessageTransformer(TaskInputMessageTransformer $taskMessageTransformer)
    {
        $this->taskInputMessageTransformer = $taskMessageTransformer;
        return $this;
    }

    /**
     * @param TaskResultMessageTransformer $taskMessageTransformer
     * @return ExecutorWorkerBuilder
     */
    public function withTaskResultMessageTransformer(TaskResultMessageTransformer $taskMessageTransformer)
    {
        $this->taskResultMessageTransformer = $taskMessageTransformer;
        return $this;
    }

    /**
     * @param TaskFactory $taskFactory
     * @return ExecutorWorkerBuilder
     */
    public function withTaskFactory(TaskFactory $taskFactory)
    {
        $this->taskFactory = $taskFactory;
        return $this;
    }

    /**
     * @param TaskRunnerSupervisor $taskRunnerSupervisor
     * @return ExecutorWorkerBuilder
     */
    public function withTaskRunnerSupervisor(TaskRunnerSupervisor $taskRunnerSupervisor)
    {
        $this->taskRunnerSupervisor = $taskRunnerSupervisor;
        return $this;
    }

    /**
     * @return Executor
     */
    public function buildExecutor()
    {
        $this->checkRequiredParameters();

        $taskScheduler = new TaskScheduler($this->queue, $this->taskInputMessageTransformer, $this->taskResultMessageTransformer);

        return new Executor($taskScheduler);
    }

    /**
     * @return Worker
     */
    public function buildWorker()
    {
        $this->checkRequiredParameters();

        $taskRunner = new TaskRunner($this->queue, $this->taskInputMessageTransformer, $this->taskFactory, $this->taskResultMessageTransformer, $this->taskRunnerSupervisor);

        return new Worker($taskRunner);
    }

    private function checkRequiredParameters()
    {
        if (!$this->queue instanceof Queue) {
            throw new \Exception('No queue was set. Use withQueue($queue) method to set one before building');
        }
    }
}
