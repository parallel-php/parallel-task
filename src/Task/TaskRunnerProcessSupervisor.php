<?php
declare(strict_types=1);

namespace ParallelTask\Task;

final class TaskRunnerProcessSupervisor implements TaskRunnerSupervisor
{
    /** @var int maximum run time in seconds */
    private $maxRunTime;
    /** @var int maximum memory increase in bytes */
    private $maxMemoryIncrease;
    /** @var int */
    private $startTime;
    /** @var int */
    private $startMemoryUsage;

    /**
     * Create a process supervisor that will take care of run time and memory increase
     * @param int $maxRunTime maximum run time in seconds
     * @param int $maxMemoryIncrease maximum memory increase in bytes
     */
    public function __construct($maxRunTime, $maxMemoryIncrease)
    {
        $this->maxRunTime = $maxRunTime;
        $this->maxMemoryIncrease = $maxMemoryIncrease;
    }

    public function markRunnerStart(): void
    {
        $this->startTime = time();
        $this->startMemoryUsage = memory_get_usage();
    }

    public function shouldRunnerStop(): bool
    {
        $peakMemoryUsage = memory_get_peak_usage();
        if ($peakMemoryUsage > $this->startMemoryUsage + $this->maxMemoryIncrease) {
            return true;
        }

        $currentTime = time();
        if ($currentTime > $this->startTime + $this->maxRunTime) {
            return true;
        }

        return false;

    }
}
