<?php
namespace ParallelTask\Queue;

abstract class NonCallbackQueue implements Queue
{
    public final function run($type, callable $runCallback)
    {
        $inputMessageWithIdentifier = $this->getInput($type);
        $inputMessage = $inputMessageWithIdentifier->getInputMessage();
        $outputMessage = $runCallback($inputMessage);
        $identifier = $inputMessageWithIdentifier->getIdentifier();
        $this->putOutput($type, $identifier, $outputMessage);
    }

    /**
     * @param string $type
     * @return InputMessageWithIdentifier
     */
    abstract public function getInput($type);

    /**
     * @param string $type
     * @param InputMessageIdentifier $identifier
     * @param OutputMessage $outputMessage
     */
    abstract public function putOutput($type, InputMessageIdentifier $identifier, OutputMessage $outputMessage);
}
