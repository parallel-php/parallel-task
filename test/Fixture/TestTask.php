<?php
declare(strict_types=1);

namespace ParallelTask\Fixture;

use ParallelTask\Task\TaskInput;
use ParallelTask\Task\TaskResult;

class TestTask implements \ParallelTask\Task\Task
{

    public function run(TaskInput $parameters): TaskResult
    {
        foreach ($parameters->getParameters() as $parameter) {
            return TaskResult::value($parameter);
        }
        throw new \RuntimeException();
    }
}
