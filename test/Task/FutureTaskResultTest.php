<?php
declare(strict_types=1);

namespace ParallelTask\Task;

class FutureTaskResultTest extends \PHPUnit\Framework\TestCase
{
    public function testResolvingFutureCallable()
    {
        $callbackTestValue = '';
        $callback = function () use (&$callbackTestValue) {
            $callbackTestValue = 'called';
            return TaskResult::value(null);
        };

        $callbackTestValue = 'not called';
        $futureTaskResult = new FutureTaskResult($callback);
        $this->assertEquals('not called', $callbackTestValue);
        $futureTaskResult->getTaskResult();
        $this->assertEquals('called', $callbackTestValue);
        $callbackTestValue = 'reset called';
        $this->assertEquals('reset called', $callbackTestValue);
        $futureTaskResult->getTaskResult();
        $this->assertEquals('reset called', $callbackTestValue);

    }
}
