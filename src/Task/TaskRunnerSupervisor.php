<?php
namespace ParallelTask\Task;

interface TaskRunnerSupervisor
{
    /**
     * Initiate the supervisor. The method will be called just before starting the infinite loop.
     */
    public function startSupervisingRunner();

    /**
     * Checks if the task runner should stop processing messages. The method will be called after processing each message.
     * @return bool
     */
    public function shouldRunnerStop();
}