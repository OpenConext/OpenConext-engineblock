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

use InvalidArgumentException;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlockBundle\Value\ExecutionTime;
use PHPUnit\Framework\TestCase;

class ExecutionTimeTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[\PHPUnit\Framework\Attributes\DataProviderExternal(\OpenConext\TestDataProvider::class, 'notInteger')]
    #[\PHPUnit\Framework\Attributes\Group('execution-time')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function execution_time_in_milliseconds_can_only_be_an_integer($notInteger)
    {
        $this->expectException(InvalidArgumentException::class);

        ExecutionTime::of($notInteger);
    }

    #[\PHPUnit\Framework\Attributes\Group('execution-time')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function execution_time_equals_a_given_other_execution_time()
    {
        $executionTime     = ExecutionTime::of(1);
        $sameExecutionTime = ExecutionTime::of(1);

        $areExecutionTimesTheSame = $executionTime->equals($sameExecutionTime);

        $this->assertTrue($areExecutionTimesTheSame);
    }

    #[\PHPUnit\Framework\Attributes\Group('execution-time')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function execution_time_does_not_equal_a_given_other_execution_time()
    {
        $executionTime          = ExecutionTime::of(1);
        $differentExecutionTime = ExecutionTime::of(2);

        $areExecutionTimesTheSame = $executionTime->equals($differentExecutionTime);

        $this->assertFalse($areExecutionTimesTheSame);
    }

    #[\PHPUnit\Framework\Attributes\Group('execution-time')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function execution_time_is_converted_to_microseconds()
    {
        $executionTime = ExecutionTime::of(1);
        $expectedExecutionTimeInMicroseconds = 1000;

        $this->assertSame($expectedExecutionTimeInMicroseconds, $executionTime->toMicroseconds());
    }
}
