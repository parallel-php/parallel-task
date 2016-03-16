<?php
namespace ParallelTask\Task;

use ParallelTask\Queue\InputMessageIdentifier;
use ParallelTask\Queue\Queue;

final class TaskScheduler
{
    /** @var Queue */
    private $queue;

    /** @var TaskInputMessageTransformer */
    private $taskInputMessageTransformer;

    /** @var TaskResultMessageTransformer */
    private $taskResultMessageTransformer;

    public function __construct(Queue $queue, TaskInputMessageTransformer $taskInputMessageTransformer, TaskResultMessageTransformer $taskResultMessageTransformer)
    {
        $this->queue = $queue;
        $this->taskInputMessageTransformer = $taskInputMessageTransformer;
        $this->taskResultMessageTransformer = $taskResultMessageTransformer;
    }

    /**
     * @param string $type
     * @param string $taskClass
     * @param TaskInput $taskInput
     */
    public function execute($type, $taskClass, TaskInput $taskInput)
    {
        $inputMessage = $this->taskInputMessageTransformer->getInputMessageFromTaskInput($taskClass, $taskInput);
        $this->queue->putInput($type, $inputMessage);
    }

    /**
     * @param string $type
     * @param string $taskClass
     * @param TaskInput $taskInput
     * @return FutureTaskResult
     */
    public function submit($type, $taskClass, TaskInput $taskInput)
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
    private function getTaskResult($type, InputMessageIdentifier $identifier)
    {
        $outputMessage = $this->queue->getOutput($type, $identifier);
        $taskResult = $this->taskResultMessageTransformer->getTaskResultFromMessage($outputMessage);

        return $taskResult;
    }
}
