<?php

use ParallelTask\Benchmark\BenchmarkTest;

require_once dirname(__DIR__) . '/../vendor/autoload.php';

$benchmark = new BenchmarkTest();
$benchmark->testSpeed();
