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

    public function __construct(
        ConsumeQueue $queue,
        TaskInputMessageTransformer $taskInputMessageTransformer,
        TaskFactory $taskFactory,
        TaskResultMessageTransformer $taskResultMessageTransformer,
        TaskRunnerSupervisor $runnerSupervisor
    ) {
        $this->queue = $queue;

        $this->taskRunCallback = function (
            InputMessage $inputMessage
        ) use (
            $taskInputMessageTransformer,
            $taskFactory,
            $taskResultMessageTransformer
        ) {
            $taskClass = $taskInputMessageTransformer->getTaskClassFromMessage($inputMessage);
            $task = $taskFactory->createTask($taskClass);
            $taskInput = $taskInputMessageTransformer->getTaskInputFromMessage($inputMessage);
            $taskResult = $this->runTask($task, $taskInput);
            return $taskResultMessageTransformer->getOutputMessageFromResult($taskResult);
        };

        $this->runnerSupervisor = $runnerSupervisor;
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

    private function runTask(Task $task, TaskInput $taskInput): TaskResult
    {
        try {
            $return = $task->run($taskInput);
            return TaskResult::fromReturn($return);
        } catch (\Exception $exception) {
            return TaskResult::fromException($exception);
        }
    }
}
