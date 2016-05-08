<?php
namespace ParallelTask\Task;

final class TaskRunnerNullSupervisor implements TaskRunnerSupervisor
{
    public function startSupervisingRunner()
    {
    }

    public function shouldRunnerStop()
    {
        return false;
    }
}
