<?php
declare(strict_types=1);

namespace ParallelTask\Task;

use ParallelTask\Queue\ConsumeQueue;
use ParallelTask\Queue\InputMessage;
use ParallelTask\Queue\OutputMessage;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class TaskRunnerTest extends \PHPUnit\Framework\TestCase
{
    /** @var ObjectProphecy */
    private $queueProphecy;
    /** @var ObjectProphecy */
    private $taskInputMessageTransformerProphecy;
    /** @var ObjectProphecy */
    private $taskFactoryProphecy;
    /** @var ObjectProphecy */
    private $taskResultMessageTransformerProphecy;
    /** @var ObjectProphecy */
    private $taskRunnerSupervisorProphecy;
    /** @var TaskRunner */
    private $sut;

    protected function setUp()
    {
        $this->queueProphecy = $this->prophesize(ConsumeQueue::class);
        $this->taskInputMessageTransformerProphecy = $this->prophesize(TaskInputMessageTransformer::class);
        $this->taskFactoryProphecy = $this->prophesize(TaskFactory::class);
        $this->taskResultMessageTransformerProphecy = $this->prophesize(TaskResultMessageTransformer::class);
        $this->taskRunnerSupervisorProphecy = $this->prophesize(TaskRunnerSupervisor::class);

        $this->sut = new TaskRunner(
            $this->queueProphecy->reveal(),
            $this->taskInputMessageTransformerProphecy->reveal(),
            $this->taskFactoryProphecy->reveal(),
            $this->taskResultMessageTransformerProphecy->reveal(),
            $this->taskRunnerSupervisorProphecy->reveal()
        );
    }

    public function testRunOnce()
    {
        $type = 'testType';
        $queueRunMethodProphecy = $this->createPropheciesForTaskRunnerRun($type);
        $queueRunMethodProphecy->shouldBeCalledTimes(1);
        $this->sut->runOnce($type);
    }

    public function testRun()
    {
        $type = 'testType';
        $this->taskRunnerSupervisorProphecy->markRunnerStart();
        $this->taskRunnerSupervisorProphecy->shouldRunnerStop()->will(function () {
            $this->shouldRunnerStop()->will(function () {
                $this->shouldRunnerStop()->will(function () {
                    $this->shouldRunnerStop()->willReturn(true);
                    return false;
                });
                return false;
            });
            return false;
        });
        $queueRunMethodProphecy = $this->createPropheciesForTaskRunnerRun($type);
        $queueRunMethodProphecy->shouldBeCalledTimes(4);
        $this->sut->run($type);
    }

    /**
     * @param $type
     */
    private function createPropheciesForTaskRunnerRun($type)
    {
        $prophet = $this;

        $queueRunMethodProphecy = $this->queueProphecy->run($type, Argument::type('callable'))->will(function (
            $parameters
        ) use ($prophet) {
            $inputMessage = new InputMessage('input' . random_int(0, 65535));
            $taskClass = 'Task' . random_int(0, 65535);
            $taskInput = new TaskInput([random_int(0, 65535)]);

            $prophet->taskInputMessageTransformerProphecy->getTaskClassFromMessage($inputMessage)->willReturn($taskClass);
            $prophet->taskInputMessageTransformerProphecy->getTaskInputFromMessage($inputMessage)->willReturn($taskInput);

            $prophet->taskFactoryProphecy->createTask($taskClass)->will(function () use (
                $taskInput,
                &$outputMessage,
                $prophet
            ) {
                $taskProphecy = $prophet->prophesize(Task::class);

                $taskRunMethodProphecy = $taskProphecy->run($taskInput)->will(function () use (
                    &$outputMessage,
                    $prophet
                ) {
                    $result = random_int(0, 65535);
                    $outputMessage = new OutputMessage('output' . random_int(0, 65535));
                    $taskResult = TaskResult::value($result);
                    $prophet->taskResultMessageTransformerProphecy->getOutputMessageFromResult($taskResult)->willReturn($outputMessage);
                    return $taskResult;
                });

                $taskRunMethodProphecy->shouldBeCalledTimes(1);
                return $taskProphecy->reveal();
            });

            $callable = $parameters[1];
            $outputMessageResult = $callable($inputMessage);
            $prophet->assertEquals($outputMessage, $outputMessageResult);
        });

        return $queueRunMethodProphecy;
    }
}
