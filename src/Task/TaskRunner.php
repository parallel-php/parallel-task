<?php
declare(strict_types=1);

namespace ParallelTask\Task;

use ParallelTask\Queue\ConsumeQueue;
use ParallelTask\Queue\InputMessage;

final class TaskRunner
{
    /** @var ConsumeQueue */
    private $queue;

    /** @var \Closure */
    private $taskRunCallback;

    /** @var TaskRunnerSupervisor */
    private $runnerSupervisor;
    /**
     * @var TaskInputMessageTransformer
     */
    private $taskInputMessageTransformer;
    /**
     * @var TaskFactory
     */
    private $taskFactory;
    /**
     * @var TaskResultMessageTransformer
     */
    private $taskResultMessageTransformer;

    public function __construct(
        ConsumeQueue $queue,
        TaskInputMessageTransformer $taskInputMessageTransformer,
        TaskFactory $taskFactory,
        TaskResultMessageTransformer $taskResultMessageTransformer,
        TaskRunnerSupervisor $runnerSupervisor
    ) {
        $this->queue = $queue;
        $this->runnerSupervisor = $runnerSupervisor;
        $this->taskInputMessageTransformer = $taskInputMessageTransformer;
        $this->taskFactory = $taskFactory;
        $this->taskResultMessageTransformer = $taskResultMessageTransformer;

        $this->taskRunCallback = function (
            InputMessage $inputMessage
        ) {
            $this->checkInputMessage($inputMessage);

            $outputMessage = $this->processInputMessage($inputMessage);

            return $outputMessage;
        };

    }

    public function run(string $type): void
    {
        $this->runnerSupervisor->markRunnerStart();

        while (true) {
            $this->queue->run($type, $this->taskRunCallback);

            if ($this->runnerSupervisor->shouldRunnerStop()) {
                break;
            }
        }
    }

    public function runOnce(string $type): void
    {
        $this->queue->run($type, $this->taskRunCallback);
    }

    private function checkInputMessage($inputMessage)
    {
    }

    private function processInputMessage($inputMessage)
    {
        $task = $this->getTask($inputMessage);
        $taskInput = $this->taskInputMessageTransformer->getTaskInputFromMessage($inputMessage);
        $taskResult = $this->runTask($task, $taskInput);

        return $this->taskResultMessageTransformer->getOutputMessageFromResult($taskResult);
    }

    private function getTask($inputMessage)
    {
        $taskClass = $this->taskInputMessageTransformer->getTaskClassFromMessage($inputMessage);

        return $this->taskFactory->createTask($taskClass);
    }

    private function runTask(Task $task, TaskInput $taskInput): TaskResult
    {
        try {
            $value = $task->run($taskInput);
            if ($value === null) {
                return TaskResult::value(null);
            }
            return $value;
        } catch (\Exception $exception) {
            return TaskResult::exception($exception);
        }
    }
}
