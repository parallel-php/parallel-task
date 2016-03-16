<?php
namespace ParallelTask;

use ParallelTask\Queue\Queue;
use ParallelTask\Task\TaskFactorySimple;
use ParallelTask\Task\TaskInputMessageTransformer;
use ParallelTask\Task\TaskMessageSerializeTransformer;
use ParallelTask\Task\TaskResultMessageTransformer;
use ParallelTask\Task\TaskRunner;
use ParallelTask\Task\TaskScheduler;

class ExecutorWorkerBuilder
{
    private $queue;
    private $taskInputMessageTransformer;
    private $taskResultMessageTransformer;
    private $taskFactory;

    public function __construct()
    {
        $this->taskInputMessageTransformer = $this->taskResultMessageTransformer = new TaskMessageSerializeTransformer();
        $this->taskFactory = new TaskFactorySimple();
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

        $taskRunner = new TaskRunner($this->queue, $this->taskInputMessageTransformer, $this->taskFactory, $this->taskResultMessageTransformer);

        return new Worker($taskRunner);
    }

    private function checkRequiredParameters()
    {
        if (!$this->queue instanceof Queue) {
            throw new \Exception('No queue was set. Use withQueue($queue) method to set one before building');
        }
    }
}
