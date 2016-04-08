<?php

namespace OpenConext\EngineBlock\Authentication\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as UnitTest;
use Ramsey\Uuid\Uuid;

class CollabPersonUuidTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $notStringOrEmptyString
     */
    public function uuid_must_be_a_non_empty_string($notStringOrEmptyString)
    {
        $this->expectException(InvalidArgumentException::class);

        new CollabPersonUuid($notStringOrEmptyString);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     */
    public function uuid_must_be_a_valid_uuid()
    {
        $this->expectException(InvalidArgumentException::class);

        new CollabPersonUuid('not a valid uuid');
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     */
    public function the_uuid_can_be_retrieved()
    {
        $uuid = (string) Uuid::uuid4();

        $collabPersonUuid = new CollabPersonUuid($uuid);

        $this->assertEquals($uuid, $collabPersonUuid->getUuid());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     */
    public function collab_person_uuids_are_equal_when_they_have_the_same_value()
    {
        $uuid = (string) Uuid::uuid4();

        $base      = new CollabPersonUuid($uuid);
        $same      = new CollabPersonUuid($uuid);
        $different = new CollabPersonUuid((string) Uuid::uuid4());

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($different));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     */
    public function a_collab_person_uuid_can_be_cast_to_string()
    {
        $collabPersonUuid = new CollabPersonUuid((string) Uuid::uuid4());

        $this->assertInternalType('string', (string) $collabPersonUuid);
    }
}
