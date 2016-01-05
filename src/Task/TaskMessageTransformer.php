<?php
namespace ParallelTask\Task;

use ParallelTask\Queue\InputMessage;
use ParallelTask\Queue\OutputMessage;

class TaskMessageTransformer
{
    /**
     * @param string $taskClass
     * @param TaskInput $taskInput
     * @return InputMessage
     */
    public function getInputMessageFromTaskInput($taskClass, TaskInput $taskInput)
    {
        $taskClassLength = strlen($taskClass);
        $paddedTaskClassLength = str_pad(dechex($taskClassLength), 4, '0', STR_PAD_LEFT);

        if (strlen($paddedTaskClassLength) > 4) {
            throw new \InvalidArgumentException('task class parameter was too long: ' . $taskClass);
        }

        $parameters = $taskInput->getParameters();
        $serializedParameters = serialize($parameters);

        $data = $paddedTaskClassLength . $taskClass . $serializedParameters;

        return new InputMessage($data);
    }

    /**
     * @param InputMessage $message
     * @return string
     */
    public function getTaskClassFromMessage(InputMessage $message)
    {
        $data = $message->getData();

        $paddedTaskClassLength = substr($data, 0, 4);
        $taskClassLength = hexdec($paddedTaskClassLength);

        $taskClass = substr($data, 4, $taskClassLength);

        return $taskClass;
    }

    /**
     * @param InputMessage $message
     * @return TaskInput
     */
    public function getTaskInputFromMessage(InputMessage $message)
    {
        $data = $message->getData();

        $paddedTaskClassLength = substr($data, 0, 4);
        $taskClassLength = hexdec($paddedTaskClassLength);

        $serializedParameters = substr($data, 4 + $taskClassLength);
        $parameters = unserialize($serializedParameters);

        return new TaskInput($parameters);
    }

    /**
     * @param TaskResult $taskResult
     * @return OutputMessage
     */
    public function getOutputMessageFromResult(TaskResult $taskResult)
    {
        try {
            $return = $taskResult->getResult();
            $serializedReturn = serialize($return);

            $data = 'r' . $serializedReturn;
        } catch (\Exception $exception) {
            $serializedException = serialize([
                get_class($exception),
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getFile(),
                $exception->getLine(),
            ]);

            $data = 'e' . $serializedException;
        }

        return new OutputMessage($data);
    }

    /**
     * @param OutputMessage $message
     * @return TaskResult
     */
    public function getTaskResultFromMessage(OutputMessage $message)
    {
        $data = $message->getData();

        $type = substr($data, 0, 1);
        $serializedData = substr($data, 1);

        if ($type === 'r') {
            $return = unserialize($serializedData);

            $taskResult = TaskResult::fromReturn($return);
        } elseif ($type === 'e') {
            list(
                $class,
                $message,
                $code,
                $file,
                $line,
                ) = unserialize($serializedData);

            $exception = $this->createResultException($class);
            $this->setResultExceptionInternalData($exception, $class, $message, $code, $file, $line);

            $taskResult = TaskResult::fromException($exception);
        } else {
            $taskResult = TaskResult::fromReturn(null);
        }

        return $taskResult;
    }

    /**
     * @param string $class
     * @return \Exception
     */
    private function createResultException($class)
    {
        $instantiator = new \Doctrine\Instantiator\Instantiator();
        $exception = $instantiator->instantiate($class);
        return $exception;
    }

    private function setResultExceptionInternalData(\Exception $exception, $class, $message, $code, $file, $line)
    {
        $this->setResultExceptionProperty($exception, $class, 'message', $message);
        $this->setResultExceptionProperty($exception, $class, 'code', $code);
        $this->setResultExceptionProperty($exception, $class, 'file', $file);
        $this->setResultExceptionProperty($exception, $class, 'line', $line);
        $this->setResultExceptionProperty($exception, \Exception::class, 'trace', [['file' => __FILE__, 'line' => 0, 'function' => 'unserialize', 'args' => []]]);
    }

    private function setResultExceptionProperty(\Exception $exception, $class, $propertyName, $propertyValue)
    {
        $codeProperty = new \ReflectionProperty($class, $propertyName);
        $codeProperty->setAccessible(true);
        $codeProperty->setValue($exception, $propertyValue);
        $codeProperty->setAccessible(false);
    }
}
