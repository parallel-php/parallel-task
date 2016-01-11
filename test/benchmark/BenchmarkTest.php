<?php
namespace ParallelTask\Benchmark;

use ParallelTask\Benchmark\QueueFactory;
use ParallelTask\Benchmark\SumTask;
use ParallelTask\Executor;
use ParallelTask\FutureResult;
use ParallelTask\Task\TaskMessageTransformer;
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
        $messageCount = 500;
        $threadCount = 4;
        $type = 'benchmark-sum';
        $workerScript = __DIR__ . '/benchmark-worker.php';

        $results = [];
        foreach ($queueTypes as $queueType) {
            $queue = $queueFactory->make($queueType);

            $transformer = new TaskMessageTransformer();
            $executor = new Executor(new TaskScheduler($queue, $transformer));

            /** @var Process[] $threads */
            $threads = [];
            for ($i = 0; $i < $threadCount; $i++) {
                $thread = new Process('php ' . $workerScript . ' ' . $queueType . ' ' . $type);
                $thread->start();
                $threads[] = $thread;
            }
            sleep(1);

            $stopwatch = Stopwatch::createStarted();

            /** @var FutureResult[] $futureResults */
            $futureResults = [];
            for ($i = 0; $i < $messageCount; $i++) {
                $futureResults[] = $executor->submit($type, SumTask::class, [$i, $i + 1]);
            }
            foreach ($futureResults as $futureResult) {
                $futureResult->getResult();
            }

            $stopwatch->stop();

            foreach ($threads as $thread) {
                $thread->stop();
            }

            $elapsed = $stopwatch->getElapsedSeconds();
            $results[$queueType] = $elapsed;
        }

        foreach ($results as $queueType => $elapsed) {
            $this->assertGreaterThan(0, $elapsed);
            $elapsed = number_format($elapsed, 2);
            echo "{$queueType}: {$elapsed}\n";
        }
    }
}
