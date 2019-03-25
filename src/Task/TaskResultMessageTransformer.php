<?php
declare(strict_types=1);

namespace ParallelTask\Task;

use ParallelTask\Queue\OutputMessage;

interface TaskResultMessageTransformer
{
    public function getOutputMessageFromResult(TaskResult $taskResult): OutputMessage;

    public function getTaskResultFromMessage(OutputMessage $message): TaskResult;
}
