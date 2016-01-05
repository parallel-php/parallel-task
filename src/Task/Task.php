<?php
namespace ParallelTask\Task;

interface Task
{
    public function run(TaskInput $parameters);
}
