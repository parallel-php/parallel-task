<?php
declare(strict_types=1);

namespace ParallelTask\Task;

final class FutureTaskResult
{
    /** @var TaskResult */
    private $taskResult;
    /** @var \Closure */
    private $taskResultCallback;
    /** @var bool */
    private $taskResultCallbackSolved = false;

    public function __construct(\Closure $taskResultCallback)
    {
        $this->taskResultCallback = $taskResultCallback;
    }

    public function getTaskResult(): TaskResult
    {
        if (!$this->taskResultCallbackSolved) {
            $this->taskResult = ($this->taskResultCallback)();
            $this->taskResultCallbackSolved = true;
        }

        return $this->taskResult;
    }
}
