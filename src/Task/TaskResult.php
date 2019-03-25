<?php
declare(strict_types=1);

namespace ParallelTask\Task;

final class TaskResult
{
    /** @var mixed */
    private $value;
    /** @var \Exception */
    private $exception;

    private function __construct()
    {
    }

    public static function exception(\Exception $exception): TaskResult
    {
        $taskResult = new TaskResult();
        $taskResult->exception = $exception;

        return $taskResult;
    }

    public static function value($value): TaskResult
    {
        $taskResult = new TaskResult();
        $taskResult->value = $value;

        return $taskResult;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function get()
    {
        if ($this->exception instanceof \Exception) {
            throw $this->exception;
        }
        return $this->value;
    }
}
