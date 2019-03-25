<?php
declare(strict_types=1);

namespace ParallelTask\Task;

use ParallelTask\Fixture\TestException;

class TaskMessageSerializeTransformerTest extends \PHPUnit\Framework\TestCase
{
    public function testExceptionSerialization()
    {

        $sut = new TaskMessageSerializeTransformer();

        $message = 'test message';
        $code = 123;
        $exception = new TestException($message, $code);

        $taskResult = TaskResult::exception($exception);

        $message = $sut->getOutputMessageFromResult($taskResult);

        $taskResult2 = $sut->getTaskResultFromMessage($message);

        try {
            $taskResult2->get();
            $this->fail('No exception was thrown');
        } catch (TestException $exception2) {
        }

        $this->assertEquals($exception->getMessage(), $exception2->getMessage());
        $this->assertEquals($exception->getCode(), $exception2->getCode());
        $this->assertEquals($exception->getFile(), $exception2->getFile());
        $this->assertEquals($exception->getLine(), $exception2->getLine());
        $this->assertCount(1, $exception2->getTrace());
    }
}
