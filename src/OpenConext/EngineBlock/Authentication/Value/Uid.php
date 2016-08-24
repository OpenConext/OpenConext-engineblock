<?php

namespace OpenConext\EngineBlock\Authentication\Value;

use OpenConext\EngineBlock\Assert\Assertion;

/**
 * Value Object representing an the value of an attribute with identifier urn:mace:dir:attribute-def:uid
 * (or urn:oid:0.9.2342.19200300.100.1.1)
 */
final class Uid
{
    const URN_MACE = 'urn:mace:dir:attribute-def:uid';

    /**
     * @var string
     */
    private $uid;

    /**
     * @param string $uid
     */
    public function __construct($uid)
    {
        Assertion::nonEmptyString($uid, 'uid');

        $this->uid = $uid;
    }

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param Uid $other
     * @return bool
     */
    public function equals(Uid $other)
    {
        return $this->uid === $other->uid;
    }

    public function __toString()
    {
        return sprintf('Uid(%s)', $this->uid);
    }
}
