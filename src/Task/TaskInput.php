<?php
namespace ParallelTask\Task;

final class TaskInput implements \ArrayAccess
{
    /** @var array */
    private $parameters;

    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    public function offsetExists($offset)
    {
        return isset($this->parameters[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->parameters[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new \Exception('Input parameters are read-only');
    }

    public function offsetUnset($offset)
    {
        throw new \Exception('Input parameters are read-only');
    }
}
