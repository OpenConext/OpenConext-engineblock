<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlock\Authentication\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class CollabPersonUuidTest extends TestCase
{
    /**
     *
     * @param mixed $notStringOrEmptyString
     */
    #[\PHPUnit\Framework\Attributes\DataProviderExternal(\OpenConext\TestDataProvider::class, 'notStringOrEmptyString')]
    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Authentication')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function uuid_must_be_a_non_empty_string($notStringOrEmptyString)
    {
        $this->expectException(InvalidArgumentException::class);

        new CollabPersonUuid($notStringOrEmptyString);
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Authentication')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function uuid_must_be_a_valid_uuid()
    {
        $this->expectException(InvalidArgumentException::class);

        new CollabPersonUuid('not a valid uuid');
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Authentication')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function the_uuid_can_be_retrieved()
    {
        $uuid = (string) Uuid::uuid4();

        $collabPersonUuid = new CollabPersonUuid($uuid);

        $this->assertEquals($uuid, $collabPersonUuid->getUuid());
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Authentication')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function collab_person_uuids_are_equal_when_they_have_the_same_value()
    {
        $uuid = (string) Uuid::uuid4();

        $base      = new CollabPersonUuid($uuid);
        $same      = new CollabPersonUuid($uuid);
        $different = new CollabPersonUuid((string) Uuid::uuid4());

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($different));
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Authentication')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function a_collab_person_uuid_can_be_cast_to_string()
    {
        $collabPersonUuid = new CollabPersonUuid((string) Uuid::uuid4());

        $this->assertIsString((string) $collabPersonUuid);
    }
}
