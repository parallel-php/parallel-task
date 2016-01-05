<?php
namespace ParallelTask\Task;

use ParallelTask\Queue\InputMessageIdentifier;
use ParallelTask\Queue\Queue;

class FutureTaskResult
{
    /** @var \Closure */
    private $taskResultCallback;

    public function __construct(\Closure $taskResultCallback)
    {
        $this->taskResultCallback = $taskResultCallback;
    }

    /**
     * @return TaskResult
     */
    public function getTaskResult()
    {
        $taskResultCallback = $this->taskResultCallback;
        $taskResult = $taskResultCallback();

        return $taskResult;
    }
}
