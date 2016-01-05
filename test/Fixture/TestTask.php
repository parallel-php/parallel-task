<?php
namespace ParallelTask\Fixture;

use ParallelTask\Task\TaskInput;

class TestTask implements \ParallelTask\Task\Task
{

    public function run(TaskInput $parameters)
    {
        foreach($parameters->getParameters() as $parameter) {
            return $parameter;
        }
        throw new \Exception();
    }
}
