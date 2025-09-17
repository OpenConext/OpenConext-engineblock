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

class CollabPersonIdTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Authentication')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function a_predictable_collab_person_id_is_generated_with_at_signs_replaced_with_underscores()
    {
        $schacHomeOrganizationValue = 'openconext.org';
        $uidValue = 'homer@domain.invalid';

        $schacHomeOrganization = new SchacHomeOrganization($schacHomeOrganizationValue);
        $uid = new Uid($uidValue);

        $collabPersonIdWithReplacedAtSign = CollabPersonId::generateWithReplacedAtSignFrom($uid, $schacHomeOrganization);

        $expectedUidValue = 'homer_domain.invalid';

        $this->assertEquals(
            CollabPersonId::URN_NAMESPACE . ':' . $schacHomeOrganizationValue . ':' . $expectedUidValue,
            $collabPersonIdWithReplacedAtSign->getCollabPersonId()
        );
    }

    /**
     *
     * @param mixed $notStringOrEmptyString
     */
    #[\PHPUnit\Framework\Attributes\DataProviderExternal(\OpenConext\TestDataProvider::class, 'notStringOrEmptyString')]
    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Authentication')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function collab_person_id_must_be_a_non_empty_string($notStringOrEmptyString)
    {
        $this->expectException(InvalidArgumentException::class);

        new CollabPersonId($notStringOrEmptyString);
    }

    /**
     *
     * @param string $wronglyNamespaced
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidNameSpaceProvider')]
    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Authentication')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function collab_person_id_must_start_with_the_correct_namespace($wronglyNamespaced)
    {
        $this->expectException(InvalidArgumentException::class);

        new CollabPersonId($wronglyNamespaced);
    }

    /**
     * @return array
     */
    public static function invalidNameSpaceProvider()
    {
        $user = ':openconext:unique-user-id';

        return [
            'no namespace'              => [$user],
            'prefixed wrong namepace'   => ['urn:not-collab:person'],
            'affixed correct namespace' => [$user . CollabPersonId::URN_NAMESPACE]
        ];
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Authentication')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function collab_person_id_must_not_be_longer_than_the_maximum_allowed_number_of_characters()
    {
        $namespaceLength = strlen(CollabPersonId::URN_NAMESPACE);
        $beneathLimit = CollabPersonId::URN_NAMESPACE . str_repeat('a', CollabPersonId::MAX_LENGTH - $namespaceLength - 1);
        $exactlyLimit = CollabPersonId::URN_NAMESPACE . str_repeat('a', CollabPersonId::MAX_LENGTH - $namespaceLength);
        $aboveLimit   = CollabPersonId::URN_NAMESPACE . str_repeat('a', CollabPersonId::MAX_LENGTH - $namespaceLength + 1);

        $exceptionCaughtAtConstruction = function ($collabPersonId) {
            try {
                new CollabPersonId($collabPersonId);
            } catch (InvalidArgumentException $exception) {
                return true;
            }

            return false;
        };

        $this->assertFalse($exceptionCaughtAtConstruction($beneathLimit));
        $this->assertFalse($exceptionCaughtAtConstruction($exactlyLimit));
        $this->assertTrue($exceptionCaughtAtConstruction($aboveLimit));
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Authentication')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function collab_person_id_can_be_retrieved()
    {
        $collabPersonIdValue = CollabPersonId::URN_NAMESPACE . ':openconext:unique-user-id';

        $collabPersonId = new CollabPersonId($collabPersonIdValue);

        $this->assertEquals($collabPersonIdValue, $collabPersonId->getCollabPersonId());
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Authentication')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function collab_person_ids_are_only_equal_if_created_with_the_same_value()
    {
        $firstId  = CollabPersonId::URN_NAMESPACE . ':openconext:unique-user-id';
        $secondId = CollabPersonId::URN_NAMESPACE . ':openconext:other-user-id';

        $base = new CollabPersonId($firstId);
        $same = new CollabPersonId($firstId);
        $different = new CollabPersonId($secondId);

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($different));
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Authentication')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function a_collab_person_id_can_be_cast_to_string()
    {
        $collabPersonIdValue = CollabPersonId::URN_NAMESPACE . ':openconext:unique-user-id';

        $collabPersonId = new CollabPersonId($collabPersonIdValue);

        $this->assertIsString((string) $collabPersonId);
    }
}
