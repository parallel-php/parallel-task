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
        $type = 'testType';
        $queueRunMethodProphecy = $this->createPropheciesForTaskRunnerRun($type);
        $queueRunMethodProphecy->shouldBeCalledTimes(1);
        $this->sut->runOnce($type);
    }

    public function testRun() {
        $type = 'testType';
        $this->taskRunnerSupervisorProphecy->startSupervisingRunner()->willReturn(null);
        $this->taskRunnerSupervisorProphecy->shouldRunnerStop()->will(function (){
            $this->shouldRunnerStop()->will(function (){
                $this->shouldRunnerStop()->will(function (){
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
            $inputMessage = new InputMessage('input' . mt_rand(0, 65535));
            $taskClass = 'Task' . mt_rand(0, 65535);
            $taskInput = new TaskInput([mt_rand(0, 65535)]);

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
                    $result = mt_rand(0, 65535);
                    $outputMessage = new OutputMessage('output' . mt_rand(0, 65535));
                    $prophet->taskResultMessageTransformerProphecy->getOutputMessageFromResult(TaskResult::fromReturn($result))->willReturn($outputMessage);
                    return $result;
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
