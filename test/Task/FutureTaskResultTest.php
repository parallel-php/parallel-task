<?php

namespace ParallelTask\Task;

class FutureTaskResultTest extends \PHPUnit_Framework_TestCase
{
    public function testResolvingFutureCallable()
    {
        $callbackTestValue = '';
        $callback = function () use (&$callbackTestValue){
            $callbackTestValue = 'called';
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
