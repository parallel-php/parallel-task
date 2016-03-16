<?php
namespace ParallelTask\Task;

class TaskFactorySimple implements TaskFactory
{
    public function createTask($taskClass)
    {
        return new $taskClass();
    }
}
