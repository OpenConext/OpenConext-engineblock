<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Serializable;
use Rhumsaa\Uuid\Uuid;

final class SamlEntityUuid implements Serializable
{
    /**
     * @var string a generated UUIDv4 used as seed for the UUIDv5.
     *
     * DO NOT CHANGE AS THIS WILL CHANGE ALL GENERATED UUIDs
     */
    private static $namespaceUuid = '85e8a14d-8650-4145-8c19-7ee4ad2c2970';

    /**
     * @var string
     */
    private $uuid;

    /**
     * By using a UUID v5 we can always generate the same UUID based on the same
     * Entity. Since this is an internal Identifier that does not need to be fully
     * random, this is a simple way to generate a non-incremental yet consistent ID.
     * This makes for instance the update of Saml Entities a lot simpler, since the
     * internal identifier does not change for a given entity, nor can it be duplicated.
     *
     * @param Entity $entity
     * @return SamlEntityUuid
     */
    public static function forEntity(Entity $entity)
    {
        return new self((string) Uuid::uuid5(self::$namespaceUuid, (string) $entity));
    }

    /**
     * @param string $uuid
     * @return SamlEntityUuid
     */
    public static function fromString($uuid)
    {
        return new self($uuid);
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

    public static function deserialize($data)
    {
        return new self($data);
    }

    public function serialize()
    {
        return $this->uuid;
    }

    /**
     * @param SamlEntityUuid $other
     * @return bool
     */
    public function equals(SamlEntityUuid $other)
    {
        return $this->uuid === $other->uuid;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    public function __toString()
    {
        return $this->uuid;
    }
}
