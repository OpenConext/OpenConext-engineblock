<?php

namespace OpenConext\EngineBlock\Authentication\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use Ramsey\Uuid\Uuid;

final class CollabPersonUuid
{
    /**
     * @var string
     */
    private $uuid;

    /**
     * @return CollabPersonUuid
     */
    public static function generate()
    {
        return new self((string) Uuid::uuid4());
    }

    /**
     * @param string $uuid
     */
    public function __construct($uuid)
    {
        Assertion::nonEmptyString($uuid, 'uuid');
        Assertion::true(
            Uuid::isValid($uuid),
            sprintf('Given string "%s" is not a valid UUID', $uuid)
        );

        $this->uuid = $uuid;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param CollabPersonUuid $other
     * @return bool
     */
    public function equals(CollabPersonUuid $other)
    {
        return $this->uuid === $other->uuid;
    }

    public function __toString()
    {
        return sprintf('CollabPersonUuid(%s)', $this->uuid);
    }
}
