<?php
declare(strict_types=1);

namespace ParallelTask\Task;

interface TaskRunnerSupervisor
{
    /**
     * Initiate the supervisor. The method will be called just before starting the infinite loop.
     */
    public function markRunnerStart(): void;

    /**
     * Checks if the task runner should stop processing messages. The method will be called after processing each message.
     */
    public function shouldRunnerStop(): bool;
}
