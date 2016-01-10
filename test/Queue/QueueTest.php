<?php
namespace ParallelTask\Queue;

abstract class QueueTest extends \PHPUnit_Framework_TestCase
{

    /** @var Queue */
    private $queueClient;

    /** @var Queue */
    private $queueServer;

    protected function setUp()
    {
        $this->queueClient = $this->getQueueInstance();
        $this->queueServer = $this->getQueueInstance();
    }

    abstract protected function getQueueInstance();

    public function testPutRun()
    {
        $type = 'test1';
        $data = "testString121";

        $inputMessage = new InputMessage($data);

        $this->queueClient->putInput($type, $inputMessage);

        $this->queueServer->run($type, function (InputMessage $inputMessage) use (&$inputMessageCapture) {
            $inputMessageCapture = $inputMessage;
            return new OutputMessage(null);
        });

        $this->assertEquals($inputMessage->getData(), $inputMessageCapture->getData());
    }

    public function testSubmitRunGet()
    {
        $type = 'test2';
        $data = "testString122in";

        $inputMessage = new InputMessage($data);

        $identifier = $this->queueClient->submitInput($type, $inputMessage);

        $this->queueServer->run($type, function (InputMessage $inputMessage) use (&$inputMessageCapture, &$outputMessageCapture) {
            $data = "testString122out";
            $outputMessage = new OutputMessage($data);
            $inputMessageCapture = $inputMessage;
            $outputMessageCapture = $outputMessage;
            return $outputMessage;
        });

        $outputMessage = $this->queueClient->getOutput($type, $identifier);

        $this->assertEquals($inputMessage->getData(), $inputMessageCapture->getData());
        $this->assertEquals($outputMessage->getData(), $outputMessageCapture->getData());
    }

}
