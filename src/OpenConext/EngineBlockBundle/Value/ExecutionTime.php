<?php

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
