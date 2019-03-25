<?php
declare(strict_types=1);

namespace ParallelTask;

use ParallelTask\Task\TaskRunner;

final class Worker
{
    /** @var TaskRunner */
    private $taskRunner;

    public function __construct(TaskRunner $taskRunner)
    {
        $this->taskRunner = $taskRunner;
    }

    public function work(string $type): void
    {
        $this->taskRunner->run($type);
    }
}
