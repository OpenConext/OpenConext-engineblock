<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Serializable;

final class GuestQualifier implements Serializable
{
    /**
     * The different qualifier options, these are fixed and communicated through SAML
     */
    const QUALIFIER_ALL  = 'All';
    const QUALIFIER_SOME = 'Some';
    const QUALIFIER_NONE = 'None';

    private static $validQualifiers = [
        self::QUALIFIER_ALL,
        self::QUALIFIER_SOME,
        self::QUALIFIER_NONE
    ];

    /**
     * @var string
     */
    private $guestQualifier;

    /**
     * @param string $guestQualifier
     */
    public function __construct($guestQualifier)
    {
        $message = sprintf(
            'GuestQualifier must be one of "GuestQualifier::%s"',
            implode('", GuestQualifier::"', self::$validQualifiers)
        );
        Assertion::inArray($guestQualifier, self::$validQualifiers, $message);

        $this->guestQualifier = $guestQualifier;
    }

    /**
     * @return GuestQualifier
     */
    public static function all()
    {
        return new self(GuestQualifier::QUALIFIER_ALL);
    }

    /**
     * @return GuestQualifier
     */
    public static function some()
    {
        return new self(GuestQualifier::QUALIFIER_SOME);
    }

    /**
     * @return GuestQualifier
     */
    public static function none()
    {
        return new self(GuestQualifier::QUALIFIER_NONE);
    }

    /**
     * @param GuestQualifier $other
     * @return bool
     */
    public function equals(GuestQualifier $other)
    {
        return $this->guestQualifier === $other->guestQualifier;
    }

    /**
     * @return string
     */
    public function getQualifier()
    {
        return $this->guestQualifier;
    }

    public static function deserialize($data)
    {
        Assertion::nonEmptyString($data, 'data');

        return new self($data);
    }

    public function serialize()
    {
        return $this->guestQualifier;
    }

    public function __toString()
    {
        return sprintf('GuestQualifier(%s)', $this->guestQualifier);
    }
}
