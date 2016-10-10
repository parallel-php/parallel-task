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
        Queue $queue,
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
