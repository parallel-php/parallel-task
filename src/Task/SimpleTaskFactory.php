<?php
namespace ParallelTask\Task;

class SimpleTaskFactory implements TaskFactory
{
    public function createTask($taskClass)
    {
        return new $taskClass();
    }
}
