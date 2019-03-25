<?php
declare(strict_types=1);

namespace ParallelTask\Task;

use ParallelTask\Queue\InputMessageIdentifier;
use ParallelTask\Queue\PublishQueue;

final class TaskScheduler
{
    /** @var PublishQueue */
    private $queue;

    /** @var TaskInputMessageTransformer */
    private $taskInputMessageTransformer;

    /** @var TaskResultMessageTransformer */
    private $taskResultMessageTransformer;

    public function __construct(PublishQueue $queue, TaskInputMessageTransformer $taskInputMessageTransformer, TaskResultMessageTransformer $taskResultMessageTransformer)
    {
        $this->queue = $queue;
        $this->taskInputMessageTransformer = $taskInputMessageTransformer;
        $this->taskResultMessageTransformer = $taskResultMessageTransformer;
    }

    public function execute(string $type, string $taskClass, TaskInput $taskInput): void
    {
        $inputMessage = $this->taskInputMessageTransformer->getInputMessageFromTaskInput($taskClass, $taskInput);
        $this->queue->putInput($type, $inputMessage);
    }

    public function submit(string $type, string $taskClass, TaskInput $taskInput): FutureTaskResult
    {
        $inputMessage = $this->taskInputMessageTransformer->getInputMessageFromTaskInput($taskClass, $taskInput);
        $identifier = $this->queue->submitInput($type, $inputMessage);

        $taskResultCallback = function () use ($type, $identifier) {
            return $this->getTaskResult($type, $identifier);
        };

        return new FutureTaskResult($taskResultCallback);
    }

    /**
     * @param string $type
     * @param InputMessageIdentifier $identifier
     * @return TaskResult
     */
    private function getTaskResult(string $type, InputMessageIdentifier $identifier): TaskResult
    {
        $outputMessage = $this->queue->getOutput($type, $identifier);

        return $this->taskResultMessageTransformer->getTaskResultFromMessage($outputMessage);
    }
}
