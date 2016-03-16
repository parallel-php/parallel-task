<?php
namespace ParallelTask;

use ParallelTask\Task\TaskInput;
use ParallelTask\Task\TaskScheduler;

final class Executor
{
    /** @var TaskScheduler */
    private $taskScheduler;

    public function __construct(TaskScheduler $taskScheduler)
    {
        $this->taskScheduler = $taskScheduler;
    }

    /**
     * @param string $type
     * @param string $taskClass
     * @param array $parameters
     */
    public function execute($type, $taskClass, $parameters)
    {
        $taskInput = new TaskInput($parameters);
        $this->taskScheduler->execute($type, $taskClass, $taskInput);
    }

    /**
     * @param string $type
     * @param string $taskClass
     * @param array $parameters
     * @return FutureResult
     */
    public function submit($type, $taskClass, $parameters)
    {
        $taskInput = new TaskInput($parameters);
        $futureTaskResult = $this->taskScheduler->submit($type, $taskClass, $taskInput);
        $futureResult = new FutureResult($futureTaskResult);

        return $futureResult;
    }
}
