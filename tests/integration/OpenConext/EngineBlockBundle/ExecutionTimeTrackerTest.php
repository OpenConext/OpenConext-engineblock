<?php

/**
 * Copyright 2010 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Tests;

use OpenConext\EngineBlockBundle\EventListener\ExecutionTimeTracker;
use OpenConext\EngineBlockBundle\Value\ExecutionTime;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Component\Stopwatch\Stopwatch;

// in order to be in control of time during our tests with the Stopwatch, we use the Symfony's ClockMock
require_once ENGINEBLOCK_FOLDER_VENDOR . '/symfony/symfony/src/Symfony/Bridge/PhpUnit/ClockMock.php';

class ExecutionTimeTrackerTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        ClockMock::register(Stopwatch::class);
        ClockMock::register(ExecutionTimeTrackerTest::class);
    }

    public function setUp(): void
    {
        ClockMock::withClockMock(0);
    }

    /**
     * @test
     * @group execution-time
     */
    public function execution_time_tracker_is_tracking_when_it_has_been_started()
    {
        $executionTimeTracker = new ExecutionTimeTracker(new Stopwatch);
        $executionTimeTracker->startTracking();

        $isTracking = $executionTimeTracker->isTracking();

        $this->assertTrue($isTracking);
    }

    /**
     * @test
     * @group execution-time
     */
    public function execution_time_tracker_is_not_tracking_when_it_has_not_been_started()
    {
        $executionTimeTracker = new ExecutionTimeTracker(new Stopwatch);

        $isTracking = $executionTimeTracker->isTracking();

        $this->assertFalse($isTracking);
    }

    /**
     * @test
     * @group execution-time
     */
    public function exceeding_of_execution_time_is_determined_correctly()
    {
        $executionTimeInMilliseconds = 10;
        $aGivenExecutionTime = ExecutionTime::of($executionTimeInMilliseconds);

        $executionTimeTracker = new ExecutionTimeTracker(new Stopwatch());
        $executionTimeTracker->startTracking();

        $this->assertFalse($executionTimeTracker->currentExecutionTimeExceeds($aGivenExecutionTime),
            sprintf(
                'Current execution time should not exceed the given execution time of (%d ms): %d ms remaining',
                $aGivenExecutionTime->getExecutionTime(),
                $executionTimeTracker->timeRemainingUntil($aGivenExecutionTime)->getExecutionTime()
            )
        );

        usleep($executionTimeInMilliseconds * 1000);

        $this->assertFalse($executionTimeTracker->currentExecutionTimeExceeds($aGivenExecutionTime),
            sprintf(
                'Current execution time should not exceed the given execution time of (%d ms) which is the same: '
                . '%d ms remaining',
                $aGivenExecutionTime->getExecutionTime(),
                $executionTimeTracker->timeRemainingUntil($aGivenExecutionTime)->getExecutionTime()
            )
        );

        usleep(1 * 1000);

        $this->assertTrue($executionTimeTracker->currentExecutionTimeExceeds($aGivenExecutionTime),
            sprintf(
                'Current execution time should exceed the given execution time of ("%d" ms) which is smaller: '
                . '"%d" ms remaining',
                $aGivenExecutionTime->getExecutionTime(),
                $executionTimeTracker->timeRemainingUntil($aGivenExecutionTime)->getExecutionTime()
            )
        );
    }

    /**
     * @test
     * @group execution-time
     */
    public function how_much_time_remains_until_a_given_time_that_is_longer_than_the_current_execution_time_is_reached_is_calculated_correctly()
    {
        $longerTimeInMilliseconds = 1000;
        $longerTime = ExecutionTime::of($longerTimeInMilliseconds);

        $executionTimeTracker = new ExecutionTimeTracker(new Stopwatch);
        $executionTimeTracker->startTracking();

        $timeRemaining = $executionTimeTracker->timeRemainingUntil($longerTime);

        $this->assertEquals($longerTimeInMilliseconds, $timeRemaining->getExecutionTime());
    }

    /**
     * @test
     * @group execution-time
     */
    public function there_is_no_time_remaining_until_a_given_time_that_is_the_same_as_the_current_execution_time()
    {
        $sameTimeInMilliseconds = 10;

        $sameTime = ExecutionTime::of($sameTimeInMilliseconds);

        $executionTimeTracker = new ExecutionTimeTracker(new Stopwatch);
        $executionTimeTracker->startTracking();

        usleep($sameTimeInMilliseconds * 1000);

        $timeRemaining = $executionTimeTracker->timeRemainingUntil($sameTime);

        $this->assertEquals(ExecutionTime::of(0), $timeRemaining);
    }
    /**
     * @test
     * @group execution-time
     */
    public function there_is_no_time_remaining_until_a_given_time_that_is_shorter_than_the_current_execution_time()
    {
        $currentExecutionTimeInMilliseconds = 10;

        $shorterTime = ExecutionTime::of(5);

        $executionTimeTracker = new ExecutionTimeTracker(new Stopwatch);
        $executionTimeTracker->startTracking();

        usleep($currentExecutionTimeInMilliseconds * 1000);

        $timeRemaining = $executionTimeTracker->timeRemainingUntil($shorterTime);

        $this->assertEquals(ExecutionTime::of(0), $timeRemaining);
    }
}
