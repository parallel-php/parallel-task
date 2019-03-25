<?php
declare(strict_types=1);

namespace ParallelTask\Task;

final class TaskRunnerNullSupervisor implements TaskRunnerSupervisor
{
    public function markRunnerStart(): void
    {
    }

    public function shouldRunnerStop(): bool
    {
        return false;
    }
}
