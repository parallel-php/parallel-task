<?php
declare(strict_types=1);

namespace ParallelTask\Queue;

abstract class NonCallbackConsumeQueue implements ConsumeQueue
{
    final public function run(string $type, callable $runCallback): void
    {
        $inputMessageWithIdentifier = $this->getInput($type);
        $inputMessage = $inputMessageWithIdentifier->getInputMessage();
        $outputMessage = $runCallback($inputMessage);
        $identifier = $inputMessageWithIdentifier->getIdentifier();
        $this->putOutput($type, $identifier, $outputMessage);
    }

    /**
     * Retrieve an input message that will be processed
     */
    abstract public function getInput(string $type): InputMessageWithIdentifier;

    /**
     * Store a result of an input processing
     */
    abstract public function putOutput(string $type, InputMessageIdentifier $identifier, OutputMessage $outputMessage): void;
}
