<?php
namespace ParallelTask\Queue;

interface Queue
{
    /**
     * Store an InputMessage and receive and identifier to be able to retrieve the output when it is ready.
     *
     * @param $type
     * @param InputMessage $inputMessage
     * @return InputMessageIdentifier
     */
    public function putInput($type, InputMessage $inputMessage);

    /**
     * The run method will fetch an InputMessage and after passing it to $runCallback will store the OutputMessage returned by it.
     *
     * @param string $type
     * @param callable $runCallback
     */
    public function run($type, callable $runCallback);

    /**
     * Retrieve the output when it's ready using the identifier obtained when putting an input.
     * This is a blocking call.
     *
     * @param string $type
     * @param InputMessageIdentifier $identifier
     * @return OutputMessage
     */
    public function getOutput($type, InputMessageIdentifier $identifier);
}
