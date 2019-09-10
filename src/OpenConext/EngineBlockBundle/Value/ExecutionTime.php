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

namespace OpenConext\EngineBlockBundle\Value;

use OpenConext\EngineBlock\Assert\Assertion;

final class ExecutionTime
{
    /**
     * @var int
     */
    private $milliseconds;

    /**
     * @param int $milliseconds
     * @return ExecutionTime
     *
     * @SuppressWarnings(PHPMD.ShortMethodName) ExecutionTime::of(100) is natural and legible
     */
    public static function of($milliseconds)
    {
        Assertion::integer($milliseconds, 'The amount of milliseconds must be an integer.');

        $executionTime               = new self();
        $executionTime->milliseconds = $milliseconds;

        return $executionTime;
    }

    private function __construct()
    {
    }

    /**
     * @param ExecutionTime $other
     * @return bool
     */
    public function equals(ExecutionTime $other)
    {
        return $this->milliseconds === $other->milliseconds;
    }

    /**
     * @return int
     */
    public function getExecutionTime()
    {
        return $this->milliseconds;
    }

    /**
     * @return int
     */
    public function toMicroseconds()
    {
        return $this->milliseconds * 1000;
    }

    public function __toString()
    {
        return sprintf('ExecutionTime(%d)', $this->milliseconds);
    }
}
