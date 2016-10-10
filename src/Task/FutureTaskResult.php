<?php
namespace ParallelTask\Task;

final class FutureTaskResult
{
    private $taskResult;
    /** @var \Closure */
    private $taskResultCallback;
    /** @var bool */
    private $taskResultCallbackSolved = false;

    public function __construct(\Closure $taskResultCallback)
    {
        $this->taskResultCallback = $taskResultCallback;
    }

    /**
     * @return TaskResult
     */
    public function getTaskResult()
    {
        if (!$this->taskResultCallbackSolved) {
            $taskResultCallback = $this->taskResultCallback;
            $this->taskResult = $taskResultCallback();
            $this->taskResultCallbackSolved = true;
        }

        return $this->taskResult;
    }
}
