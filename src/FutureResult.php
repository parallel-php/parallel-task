<?php
namespace ParallelTask;

use ParallelTask\Task\FutureTaskResult;

final class FutureResult
{
    /** @var FutureTaskResult */
    private $futureTaskResult;

    public function __construct(FutureTaskResult $futureTaskResult)
    {
        $this->futureTaskResult = $futureTaskResult;
    }

    public function getResult()
    {
        return $this->futureTaskResult->getTaskResult()->getResult();
    }
}
