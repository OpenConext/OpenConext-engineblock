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

use DateTime;
use OpenConext\EngineBlockBundle\Configuration\Feature;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use function json_decode;
use function trim;

final class DeprovisionControllerTest extends WebTestCase
{
    public function setUp(): void
    {
        $this->clearFixtures();
    }

    /**
     * @test
     * @group Api
     * @group Deprovision
     */
    public function authentication_is_required_for_accessing_the_deprovision_api()
    {
        $collabPersonId = 'urn:collab:person:test';

        $unauthenticatedClient = static::createClient();
        $unauthenticatedClient->request('GET', 'https://engine-api.dev.openconext.local/deprovision/' . $collabPersonId);
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED,  $unauthenticatedClient);

        $unauthenticatedClient = static::createClient();
        $unauthenticatedClient->request('DELETE', 'https://engine-api.dev.openconext.local/deprovision/' . $collabPersonId);
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED,  $unauthenticatedClient);

        $unauthenticatedClient = static::createClient();
        $unauthenticatedClient->request('DELETE', 'https://engine-api.dev.openconext.local/deprovision/' . $collabPersonId . '/dry-run');
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED,  $unauthenticatedClient);

        $unauthenticatedClient = static::createClient();
        $unauthenticatedClient->request('DELETE', 'https://engine-api.dev.openconext.local/remove-consent');
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED,  $unauthenticatedClient);
    }

    /**
     * @test
     * @group Api
     * @group Deprovision
     */
    public function only_get_or_delete_requests_are_allowed_when_accessing_the_deprovision_api()
    {
        $collabPersonId = 'urn:collab:person:test';

        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->getContainer()->getParameter('api.users.deprovision.username'),
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('api.users.deprovision.password'),
        ]);

        $client->request('PUT', 'https://engine-api.dev.openconext.local/deprovision/' . $collabPersonId);
        $this->assertStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, $client);

        $client->request('HEAD', 'https://engine-api.dev.openconext.local/deprovision/' . $collabPersonId);
        $this->assertStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, $client);

        $client->request('GET', 'https://engine-api.dev.openconext.local/deprovision/' . $collabPersonId . '/dry-run');
        $this->assertStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, $client);

        $this->assertResponseIsJson($client);
    }

    /**
     * @test
     * @group Api
     * @group Deprovision
     * @group FeatureToggle
     */
    public function cannot_access_the_deprovision_api_if_the_feature_has_been_disabled()
    {
        $collabPersonId = 'urn:collab:person:test';

        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->getContainer()->getParameter('api.users.deprovision.username'),
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('api.users.deprovision.password'),
        ]);

        $this->disableDeprovisionApiFeatureFor($client);

        $client->request('GET', 'https://engine-api.dev.openconext.local/deprovision/' . $collabPersonId);

        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $client);
        $this->assertResponseIsJson($client);
    }

    /**
     * @test
     * @group Api
     * @group Deprovision
     */
    public function cannot_access_the_deprovision_api_if_user_does_not_have_deprovision_role()
    {
        $collabPersonId = 'urn:collab:person:test';

        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'no_roles',
            'PHP_AUTH_PW' => 'no_roles',
        ]);

        $client->request('GET', 'https://engine-api.dev.openconext.local/deprovision/' . $collabPersonId);

        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $client);
        $this->assertResponseIsJson($client);
    }

    /**
     * @test
     * @group Api
     * @group Deprovision
     *
     * @dataProvider provideDeprovisionEndPoints
     */
    public function no_user_data_is_returned_if_collab_person_id_is_unknown($method, $path)
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->getContainer()->getParameter('api.users.deprovision.username'),
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('api.users.deprovision.password'),
        ]);

        $client->request($method, 'https://engine-api.dev.openconext.local/' . trim($path, '/'));

        $this->assertStatusCode(Response::HTTP_OK, $client);
        $this->assertResponseIsJson($client);

        $expectedData = [
            'status' => 'OK',
            'name'   => 'EngineBlock',
            'data'   => [],
        ];

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame($expectedData, $responseData);
    }

    /**
     * Test the deprovisioning API.
     *
     * This test calls every possible API method and programatically checks if
     * the correct data is deleted and/or returned.
     *
     * Reads the information but does not delete it:
     *
     *  - GET /deprovision/{collab_person_id}
     *  - DELETE /deprovision/{collab_person_id}/dry-run
     *
     * Reads the information AND deletes it:
     *
     *  - DELETE /deprovision/{collab_person_id}
     *
     * @test
     * @group Api
     * @group Deprovision
     *
     * @dataProvider provideDeprovisionEndPoints
     */
    public function all_user_data_for_collab_person_id_is_retrieved_and_deleted($method, $path)
    {
        $userId = 'urn:collab:person:test';
        $userUuid = '550e8400-e29b-41d4-a716-446655440000';
        $spEntityId1 = 'https://my-first-sp.test';
        $spEntityId2 = 'https://my-second-sp.test';
        $spUuid1 = 'd5f4944e-d929-48d7-a781-111111111111';
        $spUuid2 = 'd5f4944e-d929-48d7-a781-222222222222';
        $persistentId1 = 'persistent-id-1';
        $persistentId2 = 'persistent-id-2';
        $attributeHash = 'abe55dff15fe253d91220e945cd0f2c5f4727430';
        $consentType = 'explicit';
        $consentDate = '2017-04-18 13:37:00';

        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->getContainer()->getParameter('api.users.deprovision.username'),
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('api.users.deprovision.password'),
        ]);

        $this->addServiceProviderUuidFixture($spUuid1, $spEntityId1);
        $this->addServiceProviderUuidFixture($spUuid2, $spEntityId2);
        $this->addUserFixture($userId, $userUuid);
        $this->addSamlPersistentIdFixture($userUuid, $spUuid1, $persistentId1);
        $this->addSamlPersistentIdFixture($userUuid, $spUuid2, $persistentId2);
        $this->addConsentFixture($userId, $spEntityId1, $attributeHash, $consentType, $consentDate);
        $this->addConsentFixture($userId, $spEntityId2, $attributeHash, $consentType, $consentDate);

        $client->request($method, 'https://engine-api.dev.openconext.local/' . trim($path, '/'));

        $this->assertStatusCode(Response::HTTP_OK, $client);
        $this->assertResponseIsJson($client);

        $expectedData = [
            'status' => 'OK',
            'name'   => 'EngineBlock',
            'data' => [
                [
                    'name' => 'user',
                    'value' => [
                        'collab_person_id' => $userId,
                        'uuid' => $userUuid,
                    ]
                ],
                [
                    'name' => 'saml_persistent_id',
                    'value' => [
                        [
                            'persistent_id' => $persistentId1,
                            'user_uuid' => $userUuid,
                            'service_provider_entity_id' => $spEntityId1,
                        ],
                        [
                            'persistent_id' => $persistentId2,
                            'user_uuid' => $userUuid,
                            'service_provider_entity_id' => $spEntityId2,
                        ]
                    ]
                ],
                [
                    'name'  => 'consent',
                    'value' => [
                        [
                            'user_id' => $userId,
                            'service_provider_entity_id' => $spEntityId1,
                            'consent_given_on' => (new DateTime($consentDate))->format(DateTime::ATOM),
                            'consent_type' => $consentType,
                            'attribute_hash' => $attributeHash,
                        ],
                        [
                            'user_id' => $userId,
                            'service_provider_entity_id' => $spEntityId2,
                            'consent_given_on' => (new DateTime($consentDate))->format(DateTime::ATOM),
                            'consent_type' => $consentType,
                            'attribute_hash' => $attributeHash,
                        ],
                    ],
                ],
            ],
        ];

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals($expectedData, $responseData);

        // Now test if the data was deleted by checking the response of a subsequent call to the API.
        $client->request('GET', 'https://engine-api.dev.openconext.local/deprovision/urn:collab:person:test');
        $responseData = json_decode($client->getResponse()->getContent(), true);

        if ($method === 'DELETE' && !preg_match('#/dry-run$#', $path)) {
            $this->assertEquals(
                [
                    'status' => 'OK',
                    'name' => 'EngineBlock',
                    'data' => [],
                ],
                $responseData,
                'Expected all user data to be cleared after DELETE on /deprovision'
            );
        } else {
            $this->assertEquals($expectedData, $responseData, 'GET and dry-run API calls should be idempotent');
        }
    }

    /**
     * @return array
     */
    public function provideDeprovisionEndpoints()
    {
        return [
            ['GET', '/deprovision/urn:collab:person:test'],
            ['DELETE', '/deprovision/urn:collab:person:test'],
            ['DELETE', '/deprovision/urn:collab:person:test/dry-run'],
        ];
    }

    private function assertStatusCode($expectedStatusCode, Client $client)
    {
        $this->assertEquals($expectedStatusCode, $client->getResponse()->getStatusCode());
    }

    private function getContainer() : ContainerInterface
    {
        self::bootKernel();
        return self::$kernel->getContainer();
    }

    /**
     * @param Client $client
     */
    private function disableDeprovisionApiFeatureFor(Client $client)
    {
        $featureToggles = new FeatureConfiguration([
            'api.deprovision' => new Feature('api.deprovision', false)
        ]);
        $client->getContainer()->set('engineblock.features', $featureToggles);
    }

    /**
     * @param string $userId
     * @param string $uuid
     */
    private function addUserFixture($userId, $uuid)
    {
        $queryBuilder = $this->getContainer()->get('doctrine')->getConnection()->createQueryBuilder();
        $queryBuilder
            ->insert('user')
            ->values([
                'collab_person_id' => ':collab_person_id',
                'uuid' => ':uuid',
            ])
            ->setParameters([
                ':collab_person_id' => $userId,
                ':uuid' => $uuid,
            ])
            ->execute();
    }

    /**
     * @param string $spUuid
     * @param string $spEntityid
     */
    private function addServiceProviderUuidFixture($spUuid, $spEntityId)
    {
        $queryBuilder = $this->getContainer()->get('doctrine')->getConnection()->createQueryBuilder();
        $queryBuilder
            ->insert('service_provider_uuid')
            ->values([
                'uuid' => ':uuid',
                'service_provider_entity_id' => ':service_provider_entity_id',
            ])
            ->setParameters([
                ':uuid' => $spUuid,
                ':service_provider_entity_id' => $spEntityId,
            ])
            ->execute();
    }

    /**
     * @param string $userUuid
     * @param string $spUuid
     * @param string $persistentId
     */
    private function addSamlPersistentIdFixture($userUuid, $spUuid, $persistentId)
    {
        $queryBuilder = $this->getContainer()->get('doctrine')->getConnection()->createQueryBuilder();
        $queryBuilder
            ->insert('saml_persistent_id')
            ->values([
                'user_uuid' => ':user_uuid',
                'service_provider_uuid' => ':service_provider_uuid',
                'persistent_id' => ':persistent_id',
            ])
            ->setParameters([
                ':user_uuid' => $userUuid,
                ':service_provider_uuid' => $spUuid,
                ':persistent_id' => $persistentId,
            ])
            ->execute();
    }

    /**
     * @param string $userId
     * @param string $serviceId
     * @param string $attributeHash
     * @param string $consentType
     * @param string $consentDate
     */
    private function addConsentFixture($userId, $serviceId, $attributeHash, $consentType, $consentDate)
    {
        $queryBuilder = $this->getContainer()->get('doctrine')->getConnection()->createQueryBuilder();
        $queryBuilder
            ->insert('consent')
            ->values([
                'hashed_user_id' => ':user_id',
                'service_id'     => ':service_id',
                'attribute'      => ':attribute',
                'consent_type'   => ':consent_type',
                'consent_date'   => ':consent_date',
                'deleted_at'   => '"0000-00-00 00:00:00"',
            ])
            ->setParameters([
                ':user_id'      => sha1($userId),
                ':service_id'   => $serviceId,
                ':attribute'    => $attributeHash,
                ':consent_type' => $consentType,
                ':consent_date' => $consentDate,
            ])
            ->execute();
    }

    private function clearFixtures()
    {
        $queryBuilder = $this->getContainer()->get('doctrine')->getConnection()->createQueryBuilder();
        $queryBuilder
            ->delete('service_provider_uuid')
            ->execute();
        $queryBuilder
            ->delete('user')
            ->execute();
        $queryBuilder
            ->delete('saml_persistent_id')
            ->execute();
        $queryBuilder
            ->delete('consent')
            ->execute();
    }

    private function assertResponseIsJson(Client $client)
    {
        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }
}
