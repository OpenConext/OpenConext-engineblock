<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Serializable;

final class WorkflowState implements Serializable
{
    /**
     * Possible workflow states
     */
    const STATE_PRODACCEPTED = 'prodaccepted';
    const STATE_TESTACCEPTED = 'testaccepted';

    /**
     * @var string
     */
    private $workflowState;

    /**
     ** @param string $workflowState Either "prodaccepted" or "testaccepted"
     */
    public function __construct($workflowState)
    {
        $message = sprintf('Either "%s" or "%s"', self::STATE_PRODACCEPTED, self::STATE_TESTACCEPTED);
        Assertion::inArray($workflowState, [self::STATE_PRODACCEPTED, self::STATE_TESTACCEPTED], $message);

        $this->workflowState = $workflowState;
    }

    public static function prodaccepted()
    {
        return new self(self::STATE_PRODACCEPTED);
    }

    public static function testaccepted()
    {
        return new self(self::STATE_TESTACCEPTED);
    }

    /**
     * @param WorkflowState $other
     * @return bool
     */
    public function equals(WorkflowState $other)
    {
        return $this->workflowState === $other->workflowState;
    }

    /**
     * @return string
     */
    public function getWorkflowState()
    {
        return $this->workflowState;
    }

    public static function deserialize($data)
    {
        return new self($data);
    }

    public function serialize()
    {
        return $this->workflowState;
    }

    public function __toString()
    {
        return $this->workflowState;
    }
}
