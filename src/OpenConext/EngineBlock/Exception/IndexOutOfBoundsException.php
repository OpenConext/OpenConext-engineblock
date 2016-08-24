<?php

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
