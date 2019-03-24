<?php
declare(strict_types=1);

namespace ParallelTask\Fixture;

use ParallelTask\Queue\ConsumeQueue;
use ParallelTask\Queue\InputMessage;
use ParallelTask\Queue\InputMessageIdentifier;
use ParallelTask\Queue\OutputMessage;
use ParallelTask\Queue\PublishQueue;
use ParallelTask\Queue\Queue;

class LocalArrayTestQueue implements PublishQueue, ConsumeQueue, Queue
{
    private $storageInput = [];

    private $storageOutput = [];

    public function putInput(string $type, InputMessage $inputMessage): void
    {
        $this->submitInput($type, $inputMessage);
    }

    public function submitInput(string $type, InputMessage $inputMessage): InputMessageIdentifier
    {
        if (!isset($this->storageInput[$type])) {
            $this->storageInput[$type] = [];
        }

        $this->storageInput[$type][] = $inputMessage;
        end($this->storageInput[$type]);

        $inputMessageKey = key($this->storageInput[$type]);

        return new InputMessageIdentifier($inputMessageKey);
    }

    public function run(string $type, callable $runCallback): void
    {
        $inputMessage = reset($this->storageInput[$type]);
        $inputMessageKey = key($this->storageInput[$type]);

        $outputMessage = $runCallback($inputMessage);
        unset($this->storageInput[$type][$inputMessageKey]);
        $this->storageOutput[$inputMessageKey] = $outputMessage;
    }

    public function getOutput(string $type, InputMessageIdentifier $identifier): OutputMessage
    {
        return $this->storageOutput[$identifier->getId()];
    }

    public function __destruct()
    {
        unset($this->storageInput, $this->storageOutput);
    }
}
