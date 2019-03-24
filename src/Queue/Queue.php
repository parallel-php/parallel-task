<?php
declare(strict_types=1);

namespace ParallelTask\Queue;

interface Queue extends PublishQueue, ConsumeQueue
{
}
