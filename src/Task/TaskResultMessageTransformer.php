<?php
namespace ParallelTask\Task;

use ParallelTask\Queue\OutputMessage;

interface TaskResultMessageTransformer
{
    /**
     * @param TaskResult $taskResult
     * @return OutputMessage
     */
    public function getOutputMessageFromResult(TaskResult $taskResult);

    /**
     * @param OutputMessage $message
     * @return TaskResult
     */
    public function getTaskResultFromMessage(OutputMessage $message);
}
