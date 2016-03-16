<?php
namespace ParallelTask;

use ParallelTask\Queue\Queue;
use ParallelTask\Task\TaskFactorySimple;
use ParallelTask\Task\TaskMessageSerializeTransformer;
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
        $taskMessageTransformer = new TaskMessageSerializeTransformer();
        $taskFactory = new TaskFactorySimple();
        $taskRunner = new TaskRunner($queue, $taskMessageTransformer, $taskFactory, $taskMessageTransformer);

        return new Worker($taskRunner);
    }
}
