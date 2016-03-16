<?php
namespace ParallelTask\Task;

final class TaskFactorySimple implements TaskFactory
{
    public function createTask($taskClass)
    {
        return new $taskClass();
    }
}
