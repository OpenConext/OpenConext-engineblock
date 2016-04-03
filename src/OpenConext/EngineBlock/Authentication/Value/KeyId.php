<?php

namespace OpenConext\EngineBlock\Authentication\Value;

use OpenConext\EngineBlock\Assert\Assertion;

final class KeyId
{
    /**
     * @var string
     */
    private $keyId;

    /**
     * @param string $keyId
     */
    public function __construct($keyId)
    {
        Assertion::nonEmptyString($keyId, 'keyId');

        $this->keyId = $keyId;
    }

    /**
     * @return string
     */
    public function getKeyId()
    {
        return $this->keyId;
    }

    /**
     * @param KeyId $other
     * @return bool
     */
    public function equals(KeyId $other)
    {
        return $this->keyId === $other->keyId;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('KeyId(%s)', $this->keyId);
    }
}
