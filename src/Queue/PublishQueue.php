<?php
declare(strict_types=1);

namespace ParallelTask\Queue;

interface PublishQueue
{
    /**
     * Store an InputMessage that can be retrieved to run without capturing output.
     */
    public function putInput(string $type, InputMessage $inputMessage): void;

    /**
     * Store an InputMessage and receive and identifier to be able to retrieve the output when it is ready.
     */
    public function submitInput(string $type, InputMessage $inputMessage): InputMessageIdentifier;

    /**
     * Retrieve the output when it's ready using the identifier obtained when putting an input.
     * This is a blocking call.
     */
    public function getOutput(string $type, InputMessageIdentifier $identifier): OutputMessage;
}
