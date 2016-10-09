<?php
namespace ParallelTask\Task;

final class TaskRunnerNullSupervisor implements TaskRunnerSupervisor
{
    public function markRunnerStart()
    {
    }

    public function shouldRunnerStop()
    {
        return false;
    }
}
