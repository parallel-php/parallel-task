<?php
declare(strict_types=1);

namespace ParallelTask\Task;

final class TaskFactorySimple implements TaskFactory
{
    public function createTask(string $taskClass): Task
    {
        return new $taskClass();
    }
}
