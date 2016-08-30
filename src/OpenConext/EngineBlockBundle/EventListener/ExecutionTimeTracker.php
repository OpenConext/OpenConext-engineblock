<?php

namespace OpenConext\EngineBlockBundle\EventListener;

use OpenConext\EngineBlockBundle\Value\ExecutionTime;
use Symfony\Component\Stopwatch\Stopwatch;

final class ExecutionTimeTracker
{
    const NAME = 'execution-time';

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
        $this->stopwatch->start(self::NAME);
    }

    /**
     * @return bool
     */
    public function isTracking()
    {
        return $this->stopwatch->isStarted(self::NAME);
    }

    /**
     * @param ExecutionTime $executionTime
     * @return bool
     */
    public function currentExecutionTimeExceeds(ExecutionTime $executionTime)
    {
        $stopwatchEvent = $this->stopwatch->getEvent(self::NAME);

        return $stopwatchEvent->getDuration() > $executionTime->getExecutionTime();
    }

    /**
     * @param ExecutionTime $executionTime
     * @return ExecutionTime
     */
    public function timeRemainingUntil(ExecutionTime $executionTime)
    {
        $stopwatchEvent = $this->stopwatch->getEvent(self::NAME);

        if ($this->currentExecutionTimeExceeds($executionTime)) {
            return ExecutionTime::of(0);
        }

        $timeRemaining = $executionTime->getExecutionTime() - $stopwatchEvent->getDuration();

        return ExecutionTime::of($timeRemaining);
    }
}
