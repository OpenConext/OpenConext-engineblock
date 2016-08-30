<?php

namespace OpenConext\EngineBlockBundle\EventListener;

use OpenConext\EngineBlockBundle\Value\ExecutionTime;
use Symfony\Component\Stopwatch\Stopwatch;

final class ExecutionTimeTracker
{
    const IDENTIFIER = 'execution-time';

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
        $this->stopwatch->start(self::IDENTIFIER);
    }

    /**
     * @return bool
     */
    public function isTracking()
    {
        return $this->stopwatch->isStarted(self::IDENTIFIER);
    }

    /**
     * @param ExecutionTime $executionTime
     * @return bool
     */
    public function currentExecutionTimeExceeds(ExecutionTime $executionTime)
    {
        $stopwatchEvent = $this->stopwatch->getEvent(self::IDENTIFIER);

        return $stopwatchEvent->getDuration() > $executionTime->getExecutionTime();
    }

    /**
     * @param ExecutionTime $executionTime
     * @return ExecutionTime
     */
    public function timeRemainingUntil(ExecutionTime $executionTime)
    {
        $stopwatchEvent = $this->stopwatch->getEvent(self::IDENTIFIER);

        if ($this->currentExecutionTimeExceeds($executionTime)) {
            return ExecutionTime::of(0);
        }

        $timeRemaining = $executionTime->getExecutionTime() - $stopwatchEvent->getDuration();

        return ExecutionTime::of($timeRemaining);
    }
}
