<?php
declare(strict_types=1);

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
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->parameters[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->parameters[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException('Task input parameters are read-only');
    }

    public function offsetUnset($offset)
    {
        throw new \RuntimeException('Task input parameters are read-only');
    }
}
