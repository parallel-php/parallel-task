<?php
namespace ParallelTask;

use ParallelTask\Task\TaskInput;
use ParallelTask\Task\TaskScheduler;

final class Executor
{
    /** @var TaskScheduler */
    private $taskScheduler;

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
