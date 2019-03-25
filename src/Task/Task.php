<?php
declare(strict_types=1);

namespace ParallelTask\Task;

interface Task
{
    public function run(TaskInput $parameters): ?TaskResult;
}
