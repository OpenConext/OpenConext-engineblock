<?php

namespace OpenConext\EngineBlockBundle\EventListener;

use OpenConext\EngineBlockBundle\Value\ExecutionTime;
use Symfony\Component\Stopwatch\Stopwatch;

final class ExecutionTimeTracker
{
    const SECTION_NAME = 'execution-time';

    /**
     * @var Stopwatch
     */
    private $stopwatch;

    public function __construct(Stopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }

    /**
     * @return void
     */
    public function startTracking()
    {
        $this->stopwatch->start(self::SECTION_NAME);
    }

    /**
     * @return bool
     */
    public function isTracking()
    {
        return $this->stopwatch->isStarted(self::SECTION_NAME);
    }

    /**
     * @param ExecutionTime $executionTime
     * @return bool
     */
    public function currentExecutionTimeExceeds(ExecutionTime $executionTime)
    {
        $stopwatchEvent = $this->stopwatch->getEvent(self::SECTION_NAME);

        return $stopwatchEvent->getDuration() > $executionTime->getExecutionTime();
    }

    /**
     * @param ExecutionTime $executionTime
     * @return ExecutionTime
     */
    public function timeRemainingUntil(ExecutionTime $executionTime)
    {
        $stopwatchEvent = $this->stopwatch->getEvent(self::SECTION_NAME);

        if ($this->currentExecutionTimeExceeds($executionTime)) {
            return ExecutionTime::of(0);
        }

        $timeRemaining = $executionTime->getExecutionTime() - (int) $stopwatchEvent->getDuration();

        return ExecutionTime::of($timeRemaining);
    }
}
