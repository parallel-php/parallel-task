<?php
declare(strict_types=1);

namespace ParallelTask\Task;

use ParallelTask\Queue\InputMessage;

interface TaskInputMessageTransformer
{
    public function getInputMessageFromTaskInput(string $taskClass, TaskInput $taskInput): InputMessage;

    public function getTaskClassFromMessage(InputMessage $message): string;

    public function getTaskInputFromMessage(InputMessage $message): TaskInput;
}
