<?php
namespace ParallelTask\Task;

use ParallelTask\Queue\InputMessage;

interface TaskInputMessageTransformer
{
    /**
     * @param string $taskClass
     * @param TaskInput $taskInput
     * @return InputMessage
     */
    public function getInputMessageFromTaskInput($taskClass, TaskInput $taskInput);

    /**
     * @param InputMessage $message
     * @return string
     */
    public function getTaskClassFromMessage(InputMessage $message);

    /**
     * @param InputMessage $message
     * @return TaskInput
     */
    public function getTaskInputFromMessage(InputMessage $message);
}
