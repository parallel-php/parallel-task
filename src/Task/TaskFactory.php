<?php
namespace ParallelTask\Task;

interface TaskFactory
{
    /**
     * @param string $taskClass
     * @return Task
     */
    public function createTask($taskClass);
}
