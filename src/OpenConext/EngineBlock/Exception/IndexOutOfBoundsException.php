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

namespace OpenConext\EngineBlock\Exception;

use OutOfBoundsException;

final class IndexOutOfBoundsException extends OutOfBoundsException implements Exception
{
    /**
     * @var int
     */
    private $invalidIndex;

    /**
     * @var int|null
     */
    private $minimumIndex;

    /**
     * @var int|null
     */
    private $maximumIndex;

    /**
     * @param int $invalidIndex
     * @param int $minimumIndex
     * @return IndexOutOfBoundsException
     */
    public static function tooLow($invalidIndex, $minimumIndex)
    {
        $message = sprintf('Index "%d" is lower than the minimum index "%d"', $invalidIndex, $minimumIndex);

        $exception               = new self($message);
        $exception->invalidIndex = $invalidIndex;
        $exception->minimumIndex = $minimumIndex;

        return $exception;
    }

    /**
     * @param int $invalidIndex
     * @param int $maximumIndex
     * @return IndexOutOfBoundsException
     */
    public static function tooHigh($invalidIndex, $maximumIndex)
    {
        $message = sprintf('Index "%d" is higher than the maximum index "%d"', $invalidIndex, $maximumIndex);

        $exception               = new self($message);
        $exception->invalidIndex = $invalidIndex;
        $exception->maximumIndex = $maximumIndex;

        return $exception;
    }

    /**
     * @return int
     */
    public function getInvalidIndex()
    {
        return $this->invalidIndex;
    }

    /**
     * @return int|null
     */
    public function getMinimumIndex()
    {
        return $this->minimumIndex;
    }

    /**
     * @return int|null
     */
    public function getMaximumIndex()
    {
        return $this->maximumIndex;
    }
}
