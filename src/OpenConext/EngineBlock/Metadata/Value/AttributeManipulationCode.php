<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Serializable;

final class AttributeManipulationCode implements Serializable
{
    /**
     * @var string
     */
    private $code;

    public function __construct($code)
    {
        Assertion::nonEmptyString($code, 'code');

        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getAttributeManipulationCode()
    {
        return $this->code;
    }

    /**
     * @param AttributeManipulationCode $other
     * @return bool
     */
    public function equals(AttributeManipulationCode $other)
    {
        return $this->code === $other->code;
    }

    public static function deserialize($data)
    {
        return new self($data);
    }

    public function serialize()
    {
        return $this->code;
    }

    public function __toString()
    {
        return $this->code;
    }
}
