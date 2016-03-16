<?php
namespace ParallelTask;

use ParallelTask\Queue\Queue;
use ParallelTask\Task\SimpleTaskFactory;
use ParallelTask\Task\TaskMessageTransformer;
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

    /**
     * @param Queue $queue
     * @return Worker
     */
    public static function usingQueue(Queue $queue)
    {
        $taskMessageTransformer = new TaskMessageTransformer();
        $taskFactory = new SimpleTaskFactory();
        $taskRunner = new TaskRunner($queue, $taskMessageTransformer, $taskFactory, $taskMessageTransformer);

        return new Worker($taskRunner);
    }
}
