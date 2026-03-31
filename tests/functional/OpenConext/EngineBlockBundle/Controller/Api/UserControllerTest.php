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

namespace OpenConext\EngineBlockBundle\Tests;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

final class UserControllerTest extends FunctionalWebTestCase
{
    private const SHO = 'example.edu';
    private const UID = 'student001';
    private const SP_ENTITY_ID = 'https://sp.example.com/';

    public function tearDown(): void
    {
        $this->clearFixtures();
        parent::tearDown();
    }

    private function clearFixtures(): void
    {
        $conn = $this->connection();
        $conn->executeStatement('DELETE FROM saml_persistent_id');
        $conn->executeStatement('DELETE FROM service_provider_uuid');
        $conn->executeStatement('DELETE FROM user');
    }

    private function insertUserFixture(string $userUuid): void
    {
        $collabId = 'urn:collab:person:' . self::SHO . ':' . self::UID;
        $this->connection()->executeStatement(
            'INSERT IGNORE INTO user (collab_person_id, uuid) VALUES (?, ?)',
            [$collabId, $userUuid]
        );
    }

    private function insertSpFixture(string $spUuid): void
    {
        $this->connection()->executeStatement(
            'INSERT IGNORE INTO service_provider_uuid (uuid, service_provider_entity_id) VALUES (?, ?)',
            [$spUuid, self::SP_ENTITY_ID]
        );
    }

    private function insertPersistentIdFixture(string $persistentId, string $userUuid, string $spUuid): void
    {
        $this->connection()->executeStatement(
            'INSERT IGNORE INTO saml_persistent_id (persistent_id, user_uuid, service_provider_uuid) VALUES (?, ?, ?)',
            [$persistentId, $userUuid, $spUuid]
        );
    }

    private function connection(): Connection
    {
        return self::getContainer()->get('doctrine')->getConnection();
    }

    private function createNameIdLookupClient(): KernelBrowser
    {
        return static::createClient([], [
            'PHP_AUTH_USER' => 'nameid',
            'PHP_AUTH_PW'   => 'secret',
        ]);
    }

    #[Group('Api')]
    #[Group('NameIdLookup')]
    #[Test]
    public function nameid_endpoint_requires_authentication(): void
    {
        $client = static::createClient();
        $client->request('POST', 'https://engine-api.dev.openconext.local/info/users/nameid', [], [], ['CONTENT_TYPE' => 'application/json'], '[]');
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $client);
    }

    #[Group('Api')]
    #[Group('NameIdLookup')]
    #[Test]
    public function id_endpoint_requires_authentication(): void
    {
        $client = static::createClient();
        $client->request('POST', 'https://engine-api.dev.openconext.local/info/users/id', [], [], ['CONTENT_TYPE' => 'application/json'], '[]');
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $client);
    }

    #[Group('Api')]
    #[Group('NameIdLookup')]
    #[Test]
    public function nameid_endpoint_denies_wrong_role(): void
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'profile', 'PHP_AUTH_PW' => 'secret']);
        $client->request('POST', 'https://engine-api.dev.openconext.local/info/users/nameid', [], [], ['CONTENT_TYPE' => 'application/json'], '[]');
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $client);
    }

    // ─── HTTP method validation ───────────────────────────────────────────────

    #[Group('Api')]
    #[Group('NameIdLookup')]
    #[Test]
    public function nameid_endpoint_rejects_get_requests(): void
    {
        $client = $this->createNameIdLookupClient();
        $client->request('GET', 'https://engine-api.dev.openconext.local/info/users/nameid');
        $this->assertStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, $client);
    }

    #[Group('Api')]
    #[Group('NameIdLookup')]
    #[Test]
    public function id_endpoint_rejects_get_requests(): void
    {
        $client = $this->createNameIdLookupClient();
        $client->request('GET', 'https://engine-api.dev.openconext.local/info/users/id');
        $this->assertStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, $client);
    }

    #[Group('Api')]
    #[Group('NameIdLookup')]
    #[Test]
    public function nameid_endpoint_returns_400_for_invalid_json(): void
    {
        $client = $this->createNameIdLookupClient();
        $client->request(
            'POST',
            'https://engine-api.dev.openconext.local/info/users/nameid',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'not-json'
        );
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $client);
    }

    #[Group('Api')]
    #[Group('NameIdLookup')]
    #[Test]
    public function nameid_endpoint_returns_400_when_entry_missing_required_fields(): void
    {
        $client = $this->createNameIdLookupClient();
        $body = json_encode([['schacHomeOrganization' => 'example.edu']]);
        $client->request(
            'POST',
            'https://engine-api.dev.openconext.local/info/users/nameid',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $body
        );
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $client);
    }

    #[Group('Api')]
    #[Group('NameIdLookup')]
    #[Test]
    public function nameid_endpoint_returns_null_for_unknown_user(): void
    {
        $client = $this->createNameIdLookupClient();
        $body = json_encode([[
            'schacHomeOrganization' => 'unknown.edu',
            'uid'                   => 'nobody',
            'sp_entityid'           => self::SP_ENTITY_ID,
        ]]);
        $client->request(
            'POST',
            'https://engine-api.dev.openconext.local/info/users/nameid',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $body
        );
        $this->assertStatusCode(Response::HTTP_OK, $client);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $response);
        $this->assertNull($response[0]);
    }

    #[Group('Api')]
    #[Group('NameIdLookup')]
    #[Test]
    public function nameid_endpoint_returns_calculated_nameid_when_not_yet_stored(): void
    {
        $userUuid     = Uuid::uuid4()->toString();
        $spUuid       = Uuid::uuid4()->toString();
        $persistentId = sha1('COIN:' . $userUuid . $spUuid);

        $client = $this->createNameIdLookupClient();
        $this->insertUserFixture($userUuid);
        $this->insertSpFixture($spUuid);

        $body = json_encode([[
            'schacHomeOrganization' => self::SHO,
            'uid'                   => self::UID,
            'sp_entityid'           => self::SP_ENTITY_ID,
        ]]);
        $client->request(
            'POST',
            'https://engine-api.dev.openconext.local/info/users/nameid',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $body
        );
        $this->assertStatusCode(Response::HTTP_OK, $client);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $response);
        $this->assertSame($persistentId, $response[0]['nameid']);
        $this->assertFalse($response[0]['stored']);
    }

    #[Group('Api')]
    #[Group('NameIdLookup')]
    #[Test]
    public function nameid_endpoint_returns_stored_nameid(): void
    {
        $userUuid     = Uuid::uuid4()->toString();
        $spUuid       = Uuid::uuid4()->toString();
        $persistentId = sha1('COIN:' . $userUuid . $spUuid);

        $client = $this->createNameIdLookupClient();
        $this->insertUserFixture($userUuid);
        $this->insertSpFixture($spUuid);
        $this->insertPersistentIdFixture($persistentId, $userUuid, $spUuid);

        $body = json_encode([[
            'schacHomeOrganization' => self::SHO,
            'uid'                   => self::UID,
            'sp_entityid'           => self::SP_ENTITY_ID,
        ]]);
        $client->request(
            'POST',
            'https://engine-api.dev.openconext.local/info/users/nameid',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $body
        );
        $this->assertStatusCode(Response::HTTP_OK, $client);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($persistentId, $response[0]['nameid']);
        $this->assertTrue($response[0]['stored']);
    }

    #[Group('Api')]
    #[Group('NameIdLookup')]
    #[Test]
    public function id_endpoint_returns_null_for_unknown_nameid(): void
    {
        $client = $this->createNameIdLookupClient();
        $body = json_encode(['0000000000000000000000000000000000000000']);
        $client->request(
            'POST',
            'https://engine-api.dev.openconext.local/info/users/id',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $body
        );
        $this->assertStatusCode(Response::HTTP_OK, $client);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $response);
        $this->assertNull($response[0]);
    }

    #[Group('Api')]
    #[Group('NameIdLookup')]
    #[Test]
    public function id_endpoint_returns_user_data_for_known_nameid(): void
    {
        $userUuid     = Uuid::uuid4()->toString();
        $spUuid       = Uuid::uuid4()->toString();
        $persistentId = sha1('COIN:' . $userUuid . $spUuid);

        $client = $this->createNameIdLookupClient();
        $this->insertUserFixture($userUuid);
        $this->insertSpFixture($spUuid);
        $this->insertPersistentIdFixture($persistentId, $userUuid, $spUuid);

        $body = json_encode([$persistentId]);
        $client->request(
            'POST',
            'https://engine-api.dev.openconext.local/info/users/id',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $body
        );
        $this->assertStatusCode(Response::HTTP_OK, $client);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $response);
        $this->assertSame(self::SHO, $response[0]['schacHomeOrganization']);
        $this->assertSame(self::UID, $response[0]['uid']);
        $this->assertSame(self::SP_ENTITY_ID, $response[0]['sp_entityid']);
    }

    #[Group('Api')]
    #[Group('NameIdLookup')]
    #[Test]
    public function id_endpoint_returns_400_for_invalid_nameid_format(): void
    {
        $client = $this->createNameIdLookupClient();
        $body = json_encode(['not-a-valid-sha1']);
        $client->request(
            'POST',
            'https://engine-api.dev.openconext.local/info/users/id',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $body
        );
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $client);
    }

    private function assertStatusCode(int $expected, KernelBrowser $client): void
    {
        $this->assertSame(
            $expected,
            $client->getResponse()->getStatusCode(),
            sprintf(
                'Expected HTTP %d but got %d. Response: %s',
                $expected,
                $client->getResponse()->getStatusCode(),
                $client->getResponse()->getContent()
            )
        );
    }
}
