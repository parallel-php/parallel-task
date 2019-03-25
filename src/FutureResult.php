<?php
declare(strict_types=1);

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

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getResult()
    {
        return $this->futureTaskResult->getTaskResult()->get();
    }
}
