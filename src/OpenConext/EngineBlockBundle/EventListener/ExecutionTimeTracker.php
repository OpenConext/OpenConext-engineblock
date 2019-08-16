<?php

/**
 * Copyright 2014 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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
