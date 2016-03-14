<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use PHPUnit_Framework_TestCase as UnitTest;
use Rhumsaa\Uuid\Uuid;

class SamlEntityUuidTest extends UnitTest
{
    /**
     * Sanity check that the static UUID namespace is used.
     *
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_fixed_namespace_uuid_is_used()
    {
        $entity = new Entity(new EntityId('OpenConext.org'), EntityType::IdP());

        $uuid = Uuid::uuid5('85e8a14d-8650-4145-8c19-7ee4ad2c2970', (string) $entity);

        $samlEntityUuid = SamlEntityUuid::forEntity($entity);

        $this->assertEquals((string) $uuid, $samlEntityUuid->getUuid());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $invalidArgument
     */
    public function a_uuid_must_be_a_non_empty_string($invalidArgument)
    {
        $this->expectException(InvalidArgumentException::class);

        new SamlEntityUuid($invalidArgument);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_a_serialized_saml_entity_uuid_creates_an_equal_value_object()
    {
        $entity = new Entity(new EntityId('OpenConext.org'), EntityType::IdP());

        $original = SamlEntityUuid::forEntity($entity);
        $deserialized = SamlEntityUuid::deserialize($original->serialize());

        $this->assertTrue($original->equals($deserialized));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function uuids_are_equal_for_the_same_entity()
    {
        $baseEntity          = new Entity(new EntityId('OpenConext.org'), EntityType::IdP());
        $differentEntityId   = new Entity(new EntityId('other.invalid'), EntityType::IdP());
        $differentEntityType = new Entity(new EntityId('OpenConext.org'), EntityType::SP());

        $base            = SamlEntityUuid::forEntity($baseEntity);
        $same            = SamlEntityUuid::forEntity($baseEntity);
        $otherEntityId   = SamlEntityUuid::forEntity($differentEntityId);
        $otherEntityType = SamlEntityUuid::forEntity($differentEntityType);

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($otherEntityId));
        $this->assertFalse($base->equals($otherEntityType));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function uuids_are_equal_for_the_same_string()
    {
        $baseString = 'b0e7edf8-54b3-4195-a382-16d909602c73';
        $other      = '1329e95e-dcce-429c-ac1b-38b7c8348865';

        $base      = SamlEntityUuid::fromString($baseString);
        $same      = SamlEntityUuid::fromString($baseString);
        $different = SamlEntityUuid::fromString($other);

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($different));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function uuid_generated_for_an_entity_is_equal_to_its_uuid_as_string()
    {
        $entity = new Entity(new EntityId('OpenConext.org'), EntityType::IdP());

        $uuid = SamlEntityUuid::forEntity($entity);
        $fromString = SamlEntityUuid::fromString((string) $uuid);

        $this->assertTrue($uuid->equals($fromString));
        $this->assertSame($uuid->getUuid(), $fromString->getUuid());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_uuid_value_can_be_retrieved()
    {
        $uuid = 'b0e7edf8-54b3-4195-a382-16d909602c73';

        $samlEntityUuid = SamlEntityUuid::fromString($uuid);

        $this->assertEquals($uuid, $samlEntityUuid->getUuid());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function a_saml_entity_uuid_can_be_cast_to_string()
    {
        $uuid = 'b0e7edf8-54b3-4195-a382-16d909602c73';

        $samlEntityUuid = SamlEntityUuid::fromString($uuid);

        $this->assertSame($uuid, (string) $samlEntityUuid);
    }
}
