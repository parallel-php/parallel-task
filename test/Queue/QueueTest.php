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

    protected function tearDown()
    {
        unset($this->queueClient);
        unset($this->queueServer);
    }

    /** @return Queue */
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
            $data = substr($inputMessage->getData(), 0, -2) . 'out';
            $outputMessage = new OutputMessage($data);
            $inputMessageCapture = $inputMessage;
            $outputMessageCapture = $outputMessage;
            return $outputMessage;
        });

        $outputMessage = $this->queueClient->getOutput($type, $identifier);

        $this->assertEquals($inputMessage->getData(), $inputMessageCapture->getData());
        $this->assertEquals($outputMessage->getData(), $outputMessageCapture->getData());
    }

    public function testSubmitRunGetOnThreeMessage()
    {
        $type = 'test3';
        $data0 = "testString1230in";
        $data1 = "testString1231in";
        $data2 = "testString1232in";

        /** @var InputMessage $inputMessageCapture */
        $inputMessageCapture = null;
        /** @var OutputMessage $outputMessageCapture */
        $outputMessageCapture = null;
        $runCallback = function (InputMessage $inputMessage) use (&$inputMessageCapture, &$outputMessageCapture) {

            $data = substr($inputMessage->getData(), 0, -2) . 'out';
            $outputMessage = new OutputMessage($data);
            $inputMessageCapture = $inputMessage;
            $outputMessageCapture = $outputMessage;
            return $outputMessage;
        };

        $inputMessage = new InputMessage($data0);
        $identifier = $this->queueClient->submitInput($type, $inputMessage);
        $this->queueServer->run($type, $runCallback);
        $outputMessage = $this->queueClient->getOutput($type, $identifier);

        $this->assertEquals($inputMessage->getData(), $inputMessageCapture->getData());
        $this->assertEquals($outputMessage->getData(), $outputMessageCapture->getData());

        $inputMessage1 = new InputMessage($data1);
        $inputMessage2 = new InputMessage($data2);
        $identifier1 = $this->queueClient->submitInput($type, $inputMessage1);
        $identifier2 = $this->queueClient->submitInput($type, $inputMessage2);
        $this->queueServer->run($type, $runCallback);
        $inputMessageCapture1 = $inputMessageCapture;
        $outputMessageCapture1 = $outputMessageCapture;
        $this->queueServer->run($type, $runCallback);
        $inputMessageCapture2 = $inputMessageCapture;
        $outputMessageCapture2 = $outputMessageCapture;
        $outputMessage1 = $this->queueClient->getOutput($type, $identifier1);
        $outputMessage2 = $this->queueClient->getOutput($type, $identifier2);

        $this->assertEquals($inputMessage1->getData(), $inputMessageCapture1->getData());
        $this->assertEquals($outputMessage1->getData(), $outputMessageCapture1->getData());
        $this->assertEquals($inputMessage2->getData(), $inputMessageCapture2->getData());
        $this->assertEquals($outputMessage2->getData(), $outputMessageCapture2->getData());
    }

    public function testInvertedMessageOrder()
    {
        $type = 'test4';
        $dataIn1 = "testString1241in";
        $dataIn2 = "testString1242in";
        $dataOut1 = "testString1241out";
        $dataOut2 = "testString1242out";

        $runCallback = function (InputMessage $inputMessage) {
            $data = substr($inputMessage->getData(), 0, -2) . 'out';
            $outputMessage = new OutputMessage($data);
            return $outputMessage;
        };

        $inputMessage1 = new InputMessage($dataIn1);
        $inputMessage2 = new InputMessage($dataIn2);
        $identifier1 = $this->queueClient->submitInput($type, $inputMessage1);
        $identifier2 = $this->queueClient->submitInput($type, $inputMessage2);
        $this->queueServer->run($type, $runCallback);
        $this->queueServer->run($type, $runCallback);
        $outputMessage2 = $this->queueClient->getOutput($type, $identifier2);
        $outputMessage1 = $this->queueClient->getOutput($type, $identifier1);

        $this->assertEquals($dataOut2, $outputMessage2->getData());
        $this->assertEquals($dataOut1, $outputMessage1->getData());
    }

    public function testDifferentCallbacks()
    {
        $type = 'test5';
        $dataIn1 = "testString1251in";
        $dataIn2 = "testString1252in";
        $dataOut1 = "testString1251out";
        $dataOut2 = "testString1252out";

        $runCallback1 = function (InputMessage $inputMessage) use ($dataOut1) {
            return new OutputMessage($dataOut1);
        };
        $runCallback2 = function (InputMessage $inputMessage) use ($dataOut2) {
            return new OutputMessage($dataOut2);
        };

        $inputMessage1 = new InputMessage($dataIn1);
        $identifier1 = $this->queueClient->submitInput($type, $inputMessage1);
        $this->queueServer->run($type, $runCallback1);
        $outputMessage1 = $this->queueClient->getOutput($type, $identifier1);

        $inputMessage2 = new InputMessage($dataIn2);
        $identifier2 = $this->queueClient->submitInput($type, $inputMessage2);
        $this->queueServer->run($type, $runCallback2);
        $outputMessage2 = $this->queueClient->getOutput($type, $identifier2);

        $this->assertEquals($dataOut1, $outputMessage1->getData());
        $this->assertEquals($dataOut2, $outputMessage2->getData());
    }


}
