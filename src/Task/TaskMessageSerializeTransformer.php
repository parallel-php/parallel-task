<?php
declare(strict_types=1);

namespace ParallelTask\Task;

use ParallelTask\Queue\InputMessage;
use ParallelTask\Queue\OutputMessage;

final class TaskMessageSerializeTransformer implements TaskInputMessageTransformer, TaskResultMessageTransformer
{
    public function getInputMessageFromTaskInput(string $taskClass, TaskInput $taskInput): InputMessage
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

    public function getTaskClassFromMessage(InputMessage $message): string
    {
        $data = $message->getData();

        $paddedTaskClassLength = substr($data, 0, 4);
        $taskClassLength = hexdec($paddedTaskClassLength);

        $taskClass = substr($data, 4, $taskClassLength);

        return $taskClass;
    }

    public function getTaskInputFromMessage(InputMessage $message): TaskInput
    {
        $data = $message->getData();

        $paddedTaskClassLength = substr($data, 0, 4);
        $taskClassLength = hexdec($paddedTaskClassLength);

        $serializedParameters = substr($data, 4 + $taskClassLength);
        $parameters = unserialize($serializedParameters);

        return new TaskInput($parameters);
    }

    public function getOutputMessageFromResult(TaskResult $taskResult): OutputMessage
    {
        try {
            $value = $taskResult->get();
            $serializedValue = serialize($value);

            $data = 'r' . $serializedValue;
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

    public function getTaskResultFromMessage(OutputMessage $message): TaskResult
    {
        $data = $message->getData();

        $type = substr($data, 0, 1);
        $serializedData = substr($data, 1);

        if ($type === 'r') {
            $value = unserialize($serializedData);

            $taskResult = TaskResult::value($value);
        } elseif ($type === 'e') {
            [
                $class,
                $message,
                $code,
                $file,
                $line,
            ] = unserialize($serializedData);

            $exception = $this->createResultException($class);
            $this->setResultExceptionInternalData($exception, $class, $message, $code, $file, $line);

            $taskResult = TaskResult::exception($exception);
        } else {
            $taskResult = TaskResult::value(null);
        }

        return $taskResult;
    }

    private function createResultException(string $class): \Exception
    {
        $instantiator = new \Doctrine\Instantiator\Instantiator();
        return $instantiator->instantiate($class);
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
