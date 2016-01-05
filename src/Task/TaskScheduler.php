<?php
namespace ParallelTask\Task;

use ParallelTask\Queue\InputMessageIdentifier;
use ParallelTask\Queue\Queue;

final class TaskScheduler
{
    /** @var Queue */
    private $queue;

    /** @var TaskMessageTransformer */
    private $taskMessageTransformer;

    public function __construct(Queue $queue, TaskMessageTransformer $taskMessageTransformer)
    {
        $this->queue = $queue;
        $this->taskMessageTransformer = $taskMessageTransformer;
    }

    /**
     * @param string $type
     * @param string $taskClass
     * @param TaskInput $taskInput
     * @return FutureTaskResult
     */
    public function submit($type, $taskClass, TaskInput $taskInput)
    {
        $inputMessage = $this->taskMessageTransformer->getInputMessageFromTaskInput($taskClass, $taskInput);
        $identifier = $this->queue->putInput($type, $inputMessage);

        $taskResultCallback = function () use($type, $identifier) {
            return $this->getTaskResult($type, $identifier);
        };

        return new FutureTaskResult($taskResultCallback);
    }

    /**
     * @param string $type
     * @param InputMessageIdentifier $identifier
     * @return TaskResult
     */
    private function getTaskResult($type, InputMessageIdentifier $identifier)
    {
        $outputMessage = $this->queue->getOutput($type, $identifier);
        $taskResult = $this->taskMessageTransformer->getTaskResultFromMessage($outputMessage);

        return $taskResult;
    }
}
