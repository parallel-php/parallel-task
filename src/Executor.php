<?php
declare(strict_types=1);

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

    public function execute(string $type, string $taskClass, array $parameters): void
    {
        $taskInput = new TaskInput($parameters);
        $this->taskScheduler->execute($type, $taskClass, $taskInput);
    }

    public function submit(string $type, string $taskClass, array $parameters): FutureResult
    {
        $taskInput = new TaskInput($parameters);
        $futureTaskResult = $this->taskScheduler->submit($type, $taskClass, $taskInput);

        return new FutureResult($futureTaskResult);
    }
}
