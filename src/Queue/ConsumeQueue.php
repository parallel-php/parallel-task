<?php
declare(strict_types=1);

namespace ParallelTask\Queue;

interface ConsumeQueue
{
    /**
     * The run method will fetch an InputMessage and after passing it to $runCallback will store the OutputMessage returned by it.
     */
    public function run(string $type, callable $runCallback): void;
}
