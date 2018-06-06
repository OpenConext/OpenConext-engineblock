<?php

namespace OpenConext\EngineBlock\Authentication\Value;

use JsonSerializable;
use OpenConext\EngineBlock\Assert\Assertion;

final class ConsentType implements JsonSerializable
{
    const TYPE_EXPLICIT = 'explicit';
    const TYPE_IMPLICIT = 'implicit';

    /**
     * @var string
     */
    private $consentType;

    /**
     * @return ConsentType
     */
    public static function explicit()
    {
        return new self(self::TYPE_EXPLICIT);
    }

    /**
     * @return ConsentType
     */
    public static function implicit()
    {
        return new self(self::TYPE_IMPLICIT);
    }

    /**
     * @param ConsentType::TYPE_EXPLICIT|ConsentType::TYPE_IMPLICIT $consentType
     *
     * @deprecated Use the implicit and explicit named constructors. Will be removed
     *             when Doctrine ORM is implemented.
     */
    public function __construct($consentType)
    {
        Assertion::choice(
            $consentType,
            [self::TYPE_EXPLICIT, self::TYPE_IMPLICIT],
            'ConsentType must be one of ConsentType::TYPE_EXPLICIT, ConsentType::TYPE_IMPLICIT'
        );

        $this->consentType = $consentType;
    }

    /**
     * @param ConsentType $other
     * @return bool
     */
    public function equals(ConsentType $other)
    {
        return $this->consentType === $other->consentType;
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->consentType;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->consentType;
    }
}
