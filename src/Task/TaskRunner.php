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

    /** @var TaskRunnerSupervisor */
    private $runnerSupervisor;

    public function __construct(
        Queue $queue,
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

    public function run($type)
    {
        $this->runnerSupervisor->markRunnerStart();

        while (true) {
            $this->queue->run($type, $this->taskRunCallback);
            
            if ($this->runnerSupervisor->shouldRunnerStop()) {
                break;
            }
        }
    }

    public function runOnce($type)
    {
        $this->queue->run($type, $this->taskRunCallback);
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
