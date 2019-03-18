<?php

namespace OpenConext\EngineBlockBundle\Tests;

use Mockery;
use OpenConext\EngineBlockBundle\EventListener\ExecutionTimeTracker;
use OpenConext\EngineBlockBundle\Value\ExecutionTime;
use PHPUnit_Framework_TestCase as TestCase;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

class ExecutionTimeTrackerEventListenerTest extends TestCase
{
    /**
     * @test
     * @group execution-time
     */
    public function execution_time_tracker_is_tracking_when_it_has_been_started()
    {
        $stopwatch = Mockery::mock(Stopwatch::class);
        $stopwatch
            ->shouldReceive('start')
            ->with(ExecutionTimeTracker::SECTION_NAME)
            ->once();
        $stopwatch
            ->shouldReceive('isStarted')
            ->once()
            ->andReturn(true);

        $executionTimeTracker = new ExecutionTimeTracker($stopwatch);
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
        $stopwatch = Mockery::mock(Stopwatch::class);
        $stopwatch
            ->shouldReceive('isStarted')
            ->once()
            ->andReturn(false);

        $executionTimeTracker = new ExecutionTimeTracker($stopwatch);

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

        $stopwatchEvent = Mockery::mock(StopwatchEvent::class);

        $stopwatch = Mockery::mock(Stopwatch::class);
        $stopwatch
            ->shouldReceive('start')
            ->with(ExecutionTimeTracker::SECTION_NAME);
        $stopwatch
            ->shouldReceive('getEvent')
            ->andReturn($stopwatchEvent);

        $executionTimeTracker = new ExecutionTimeTracker($stopwatch);
        $executionTimeTracker->startTracking();

        $stopwatchEvent
            ->shouldReceive('getDuration')
            ->times(3)
            ->andReturn(0);

        $this->assertFalse($executionTimeTracker->currentExecutionTimeExceeds($aGivenExecutionTime),
            sprintf(
                'Current execution time should not exceed the given execution time of (%d ms): %d ms remaining',
                $aGivenExecutionTime->getExecutionTime(),
                $executionTimeTracker->timeRemainingUntil($aGivenExecutionTime)->getExecutionTime()
            )
        );

        $stopwatchEvent
            ->shouldReceive('getDuration')
            ->times(3)
            ->andReturn($executionTimeInMilliseconds);

        $this->assertFalse($executionTimeTracker->currentExecutionTimeExceeds($aGivenExecutionTime),
            sprintf(
                'Current execution time should not exceed the given execution time of (%d ms) which is the same: '
                . '%d ms remaining',
                $aGivenExecutionTime->getExecutionTime(),
                $executionTimeTracker->timeRemainingUntil($aGivenExecutionTime)->getExecutionTime()
            )
        );

        $stopwatchEvent
            ->shouldReceive('getDuration')
            ->twice()
            ->andReturn($executionTimeInMilliseconds + 1);

        $this->assertTrue($executionTimeTracker->currentExecutionTimeExceeds($aGivenExecutionTime),
            sprintf(
                'Current execution time should exceed the given execution time of (%d ms) which is smaller: '
                . '%d ms remaining',
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

        $stopwatchEvent = Mockery::mock(StopwatchEvent::class);

        $stopwatch = Mockery::mock(Stopwatch::class);
        $stopwatch
            ->shouldReceive('start')
            ->with(ExecutionTimeTracker::SECTION_NAME);
        $stopwatch
            ->shouldReceive('getEvent')
            ->andReturn($stopwatchEvent);

        $executionTimeTracker = new ExecutionTimeTracker($stopwatch);
        $executionTimeTracker->startTracking();

        $stopwatchEvent
            ->shouldReceive('getDuration')
            ->andReturn(0);

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

        $stopwatchEvent = Mockery::mock(StopwatchEvent::class);

        $stopwatch = Mockery::mock(Stopwatch::class);
        $stopwatch
            ->shouldReceive('start')
            ->with(ExecutionTimeTracker::SECTION_NAME);
        $stopwatch
            ->shouldReceive('getEvent')
            ->andReturn($stopwatchEvent);

        $executionTimeTracker = new ExecutionTimeTracker($stopwatch);
        $executionTimeTracker->startTracking();

        $stopwatchEvent
            ->shouldReceive('getDuration')
            ->andReturn($sameTimeInMilliseconds);

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

        $stopwatchEvent = Mockery::mock(StopwatchEvent::class);

        $stopwatch = Mockery::mock(Stopwatch::class);
        $stopwatch
            ->shouldReceive('start')
            ->with(ExecutionTimeTracker::SECTION_NAME);
        $stopwatch
            ->shouldReceive('getEvent')
            ->andReturn($stopwatchEvent);

        $executionTimeTracker = new ExecutionTimeTracker($stopwatch);
        $executionTimeTracker->startTracking();

        $stopwatchEvent
            ->shouldReceive('getDuration')
            ->andReturn($currentExecutionTimeInMilliseconds);

        $timeRemaining = $executionTimeTracker->timeRemainingUntil($shorterTime);

        $this->assertEquals(ExecutionTime::of(0), $timeRemaining);
    }
}
