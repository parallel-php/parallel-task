<?php
namespace ParallelTask\Task;

use ParallelTask\Queue\InputMessage;
use ParallelTask\Queue\Queue;

final class TaskRunner
{
    /** @var Queue */
    private $queue;

    /** @var \Closure */
    private $taskRunCallback;

    public function __construct(Queue $queue, TaskMessageTransformer $taskMessageTransformer)
    {
        $this->queue = $queue;

        $this->taskRunCallback = function (InputMessage $inputMessage) use ($taskMessageTransformer) {
            $taskClass = $taskMessageTransformer->getTaskClassFromMessage($inputMessage);
            $task = $this->createTask($taskClass);
            $taskInput = $taskMessageTransformer->getTaskInputFromMessage($inputMessage);
            $taskResult = $this->runTask($task, $taskInput);
            return $taskMessageTransformer->getOutputMessageFromResult($taskResult);
        };
    }

    public function run($type)
    {
        while (true) {
            $this->queue->run($type, $this->taskRunCallback);
        }
    }

    public function runOnce($type)
    {
        $this->queue->run($type, $this->taskRunCallback);
    }

    /**
     * @param string $taskClass
     * @return Task
     */
    private function createTask($taskClass)
    {
        return new $taskClass();
    }

    /**
     * @param Task $task
     * @param TaskInput $taskInput
     * @return TaskResult
     */
    private function runTask($task, TaskInput $taskInput)
    {
        try {
            $return = $task->run($taskInput);
            return TaskResult::fromReturn($return);
        } catch (\Exception $exception) {
            return TaskResult::fromException($exception);
        }
    }
}
