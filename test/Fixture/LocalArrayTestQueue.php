<?php
namespace ParallelTask\Fixture;

use ParallelTask\Queue\InputMessage;
use ParallelTask\Queue\InputMessageIdentifier;
use ParallelTask\Queue\Queue;

class LocalArrayTestQueue implements Queue
{
    private $storageInput = [];

    private $storageOutput = [];

    public function putInput($type, InputMessage $inputMessage)
    {
        $this->submitInput($type, $inputMessage);
    }

    public function submitInput($type, InputMessage $inputMessage)
    {
        if (!isset($this->storageInput[$type])) {
            $this->storageInput[$type] = [];
        }

        $this->storageInput[$type][] = $inputMessage;
        end($this->storageInput[$type]);

        $inputMessageKey = key($this->storageInput[$type]);

        return new InputMessageIdentifier($inputMessageKey);
    }

    public function run($type, callable $runCallback)
    {
        $inputMessage = reset($this->storageInput[$type]);
        $inputMessageKey = key($this->storageInput[$type]);

        $outputMessage = $runCallback($inputMessage);
        unset($this->storageInput[$type][$inputMessageKey]);
        $this->storageOutput[$inputMessageKey] = $outputMessage;
    }

    public function getOutput($type, InputMessageIdentifier $identifier)
    {
        return $this->storageOutput[$identifier->getId()];
    }
}
