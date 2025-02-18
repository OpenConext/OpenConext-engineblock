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

namespace OpenConext\EngineBlock\Metadata;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MfaEntityCollectionTest extends TestCase
{
    public function test_works_with_empty_data()
    {
        $collection = MfaEntityCollection::fromMetadataPush([]);
        $this->assertCount(0, $collection);
    }

    public function test_works_with_correct_data()
    {
        $collection = MfaEntityCollection::fromMetadataPush($this->validData());
        $this->assertCount(2, $collection);
        $entity = $collection->findByEntityId('https://teams.dev.openconext.local/shibboleth');
        $this->assertInstanceOf(MfaEntity::class, $entity);
        $this->assertEquals('https://teams.dev.openconext.local/shibboleth', $entity->entityId());
        $this->assertEquals('http://schemas.microsoft.com/claims/multipleauthn', $entity->level());

        $entity = $collection->findByEntityId('https://aa.dev.openconext.local/shibboleth');
        $this->assertInstanceOf(MfaEntity::class, $entity);
        $this->assertEquals('https://aa.dev.openconext.local/shibboleth', $entity->entityId());
        $this->assertEquals('http://schemas.microsoft.com/claims/multipleauthn', $entity->level());
    }

    public function test_find_by_can_return_null()
    {
        $data = [
            [
                "name" => "https://teams.dev.openconext.local/shibboleth",
                "level" => "http://schemas.microsoft.com/claims/multipleauthn",
            ],
        ];
        $collection = MfaEntityCollection::fromMetadataPush($data);
        $this->assertNull($collection->findByEntityId('tjoeptjoep'));
    }

    public function test_rejects_duplicate_entity_ids()
    {
        $data = [
            [
                "name" => "https://teams.dev.openconext.local/shibboleth",
                "level" => "http://schemas.microsoft.com/claims/multipleauthn",
            ],
            [
                "name" => "https://teams.dev.openconext.local/shibboleth",
                "level" => "http://schemas.microsoft.com/claims/multipleauthn",
            ],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate SP entity ids are not allowed');
        MfaEntityCollection::fromMetadataPush($data);
    }

    private function validData(): array
    {
        return [
            [
                "name" => "https://teams.dev.openconext.local/shibboleth",
                "level" => "http://schemas.microsoft.com/claims/multipleauthn",
            ],
            [
                "name" => "https://aa.dev.openconext.local/shibboleth",
                "level" => "http://schemas.microsoft.com/claims/multipleauthn",
            ],
        ];
    }
}
