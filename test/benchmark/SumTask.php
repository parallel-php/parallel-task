<?php
namespace ParallelTask\Benchmark;

use ParallelTask\Task\Task;
use ParallelTask\Task\TaskInput;

class SumTask implements Task
{
    public function run(TaskInput $parameters)
    {
        usleep(5000);
        $sum = array_sum($parameters->getParameters());
        echo $sum . "\n";
        return $sum;
    }
}
