<?php
namespace ParallelTask\Task;

final class TaskResult
{
    private $return;
    private $exception;

    private function __construct()
    {
    }

    public static function fromException(\Exception $exception)
    {
        $taskResult = new TaskResult();
        $taskResult->exception = $exception;

        return $taskResult;
    }

    public static function fromReturn($return)
    {
        $taskResult = new TaskResult();
        $taskResult->return = $return;

        return $taskResult;
    }

    public function getResult()
    {
        if ($this->exception instanceof \Exception) {
            throw $this->exception;
        }
        return $this->return;
    }
}
