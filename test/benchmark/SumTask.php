<?php
namespace ParallelTask\Benchmark;

use ParallelTask\Task\Task;
use ParallelTask\Task\TaskInput;
use ParallelTask\Task\TaskResult;

class SumTask implements Task
{
    public function run(TaskInput $parameters): TaskResult
    {
        usleep(5000);
        $sum = array_sum($parameters->getParameters());
        echo $sum . "\n";
        return TaskResult::value($sum);
    }
}
