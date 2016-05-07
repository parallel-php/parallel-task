<?php
namespace ParallelTask\Task;

class TaskRunnerNullSupervisor implements TaskRunnerSupervisor
{
    public function startSupervisingRunner()
    {
    }

    public function shouldRunnerStop()
    {
        return false;
    }
}
