<?php
declare(strict_types=1);

namespace ParallelTask\Task;

interface TaskFactory
{
    public function createTask(string $taskClass): Task;
}
