<?php
namespace ParallelTask\Task;

use ParallelTask\Queue\InputMessage;
use ParallelTask\Queue\OutputMessage;
use ParallelTask\Queue\Queue;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class TaskRunnerTest extends \PHPUnit_Framework_TestCase
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
        $this->queueProphecy = $this->prophesize(Queue::class);
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

    public function testRunOnce() {
        //given
        $type = 'testType';

        $inputMessage = new InputMessage('input');
        $outputMessage = new OutputMessage('output');

        $taskClass = 'Task';
        $taskProphecy = $this->prophesize(Task::class);
        $taskInput = new TaskInput([]);
        $result = 'x';
        $taskResult = TaskResult::fromReturn($result);

        $this->taskInputMessageTransformerProphecy->getTaskClassFromMessage($inputMessage)->willReturn($taskClass);
        $this->taskFactoryProphecy->createTask($taskClass)->willReturn($taskProphecy->reveal());
        $this->taskInputMessageTransformerProphecy->getTaskInputFromMessage($inputMessage)->willReturn($taskInput);
        $taskRunMethodProphecy = $taskProphecy->run($taskInput)->willReturn($result);
        $this->taskResultMessageTransformerProphecy->getOutputMessageFromResult($taskResult)->willReturn($outputMessage);

        $this->queueProphecy->run($type, Argument::type('callable'))->will(function($parameters) use ($inputMessage, &$outputMessageResult) {
            $callable = $parameters[1];
            $outputMessageResult = $callable($inputMessage);
        });

        //when
        $this->sut->runOnce($type);

        //then
        $taskRunMethodProphecy->shouldHaveBeenCalledTimes(1);
        $this->assertEquals($outputMessage, $outputMessageResult);
    }




}
