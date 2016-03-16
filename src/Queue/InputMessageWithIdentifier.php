<?php
namespace ParallelTask\Queue;

final class InputMessageWithIdentifier
{
    /** @var InputMessageIdentifier */
    private $identifier;
    /** @var InputMessage */
    private $inputMessage;

    public function __construct(InputMessageIdentifier $identifier, InputMessage $inputMessage)
    {
        $this->identifier = $identifier;
        $this->inputMessage = $inputMessage;
    }

    /**
     * @return InputMessageIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return InputMessage
     */
    public function getInputMessage()
    {
        return $this->inputMessage;
    }
}
