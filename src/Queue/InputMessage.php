<?php
namespace ParallelTask\Queue;

final class InputMessage
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
