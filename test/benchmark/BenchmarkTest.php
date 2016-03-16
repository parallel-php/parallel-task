<?php
namespace ParallelTask\Benchmark;

use ParallelTask\Executor;
use ParallelTask\FutureResult;
use ParallelTask\Task\TaskMessageSerializeTransformer;
use ParallelTask\Task\TaskScheduler;
use Symfony\Component\Process\Process;
use TimeBenchmark\Stopwatch;

class BenchmarkTest extends \PHPUnit_Framework_TestCase
{
    public function testSpeed()
    {
        $queueTypes = [
            'redis',
            'predis',
            'rabbitMQ',
        ];

        $queueFactory = new QueueFactory();
        $messageCount = 1000;
        $threadCount = 4;
        $type = 'benchmark-sum';
        $workerScript = __DIR__ . '/benchmark-worker.php';

        $results = [];
        foreach ($queueTypes as $queueType) {
            $queue = $queueFactory->make($queueType);

            $transformer = new TaskMessageSerializeTransformer();
            $executor = new Executor(new TaskScheduler($queue, $transformer, $transformer));

            /** @var Process[] $threads */
            $threads = [];
            for ($i = 0; $i < $threadCount; $i++) {
                $thread = new Process('php ' . $workerScript . ' ' . $queueType . ' ' . $type);
                $thread->start();
                $threads[] = $thread;
            }
            sleep(2);

            $stopwatch = Stopwatch::createStarted();

            /** @var FutureResult[] $futureResults */
            $futureResults = [];
            for ($i = 0; $i < $messageCount; $i++) {
                $futureResults[] = $executor->submit($type, SumTask::class, [$i, $i + 1]);
            }
            $stopwatch->pause();
            $submitTime = $stopwatch->getElapsedSeconds();
            $stopwatch->resume();

            foreach ($futureResults as $futureResult) {
                $futureResult->getResult();
            }

            $stopwatch->stop();
            $totalTime = $stopwatch->getElapsedSeconds();

            foreach ($threads as $thread) {
                $thread->stop();
            }

            $results[$queueType] = [$totalTime, $submitTime];
        }

        foreach ($results as $queueType => list($totalTime, $submitTime)) {
            $this->assertGreaterThan(0, $totalTime);
            $totalTime = number_format($totalTime, 2);
            $submitTime = number_format($submitTime, 2);
            echo "{$queueType}: total = {$totalTime} seconds, submit = {$submitTime} seconds\n";
        }
    }
}
