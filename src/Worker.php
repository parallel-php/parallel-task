<?php
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

    /**
     * @param string $type
     */
    public function work($type)
    {
        $this->taskRunner->run($type);
    }
}
