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
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Mdui;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlockBundle\Configuration\Feature;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use OpenConext\Value\Saml\NameIdFormat;
use PDOStatement;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use function json_decode;
use function json_encode;

final class ConsentControllerTest extends WebTestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->clearConsentFixtures();
        $this->clearMetadataFixtures();

        parent::__construct($name, $data, $dataName);

    }

    public function tearDown(): void
    {
        $this->clearConsentFixtures();
        $this->clearMetadataFixtures();
    }

    /**
     * @test
     * @group Api
     * @group Consent
     * @group Profile
     */
    public function authentication_is_required_for_accessing_the_consent_api()
    {
        $userId = 'my-name-id';

        $unauthenticatedClient = static::createClient();
        $unauthenticatedClient->request('GET', 'https://engine-api.dev.openconext.local/consent/' . $userId);
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED,  $unauthenticatedClient);

        $unauthenticatedClient = static::createClient();
        $unauthenticatedClient->request('POST', 'https://engine-api.dev.openconext.local/remove-consent');
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED,  $unauthenticatedClient);

    }

    /**
     * @test
     * @group Api
     * @group Consent
     * @group Profile
     *
     * @dataProvider invalidHttpMethodProvider
     * @param string $invalidHttpMethod
     */
    public function only_get_requests_are_allowed_when_accessing_the_consent_api($invalidHttpMethod)
    {
        $userId = 'my-name-id';

        $client = $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->getContainer()->getParameter('api.users.profile.username'),
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $client->request($invalidHttpMethod, 'https://engine-api.dev.openconext.local/consent/' . $userId);
        $this->assertStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Consent
     * @group Profile
     * @group FeatureToggle
     */
    public function cannot_access_the_consent_api_if_the_feature_has_been_disabled()
    {
        $userId = 'my-name-id';

        $client = $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->getContainer()->getParameter('api.users.profile.username'),
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $this->disableConsentApiFeatureFor($client);

        $client->request('GET', 'https://engine-api.dev.openconext.local/consent/' . $userId);
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Consent
     * @group Profile
     */
    public function cannot_access_the_consent_api_if_user_does_not_have_profile_role()
    {
        $userId = 'my-name-id';

        $client = $client = static::createClient([], [
            'PHP_AUTH_USER' => 'no_roles',
            'PHP_AUTH_PW' => 'no_roles',
        ]);

        $client->request('GET', 'https://engine-api.dev.openconext.local/consent/' . $userId);

        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Consent
     * @group Profile
     */
    public function a_consent_listing_for_a_not_found_user_is_retrieved_as_an_empty_array_from_the_consent_api()
    {
        $userId = 'my-name-id';

        $client = $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->getContainer()->getParameter('api.users.profile.username'),
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $client->request('GET', 'https://engine-api.dev.openconext.local/consent/' . $userId);

        $this->assertStatusCode(Response::HTTP_OK, $client);
        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');

        $expectedData = [];
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame($expectedData, $responseData);
    }

    /**
     * @test
     * @group Api
     * @group Consent
     * @group Profile
     */
    public function a_consent_listing_for_a_given_user_is_retrieved_from_the_consent_api()
    {
        $userId = 'my-name-id';
        $spEntityId = 'https://my-test-sp.test';
        $attributeHash = 'abe55dff15fe253d91220e945cd0f2c5f4727430';
        $consentType = 'explicit';
        $consentDate = '2017-04-18 13:37:00';
        $deletedAt = '0000-00-00 00:00:00';

        $technicalContact = new ContactPerson('technical');
        $technicalContact->emailAddress = 'technical@my-test-sp.test';
        $firstSupportContact = new ContactPerson('support');
        $firstSupportContact->emailAddress = 'first-support@my-test-sp.test';
        $secondSupportContact = new ContactPerson('support');
        $secondSupportContact->emailAddress = 'second-support@my-test-sp.test';

        $json = '{"DisplayName":{"name":"DisplayName","values":{"en":{"value":"DisplayName EN","language":"en"},"nl":{"value":"DisplayName NL","language":"nl"},"pt":{"value":"DisplayName PT","language":"pt"}}},"Description":{"name":"Description","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Keywords":{"name":"Keywords","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Logo":{"name":"Logo","url":"https:\/\/link-to-my.logo.example.org\/img\/logo.png","width":null,"height":null},"PrivacyStatementURL":{"name":"PrivacyStatementURL","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}}}';
        // Create a value object from json
        $mdui = Mdui::fromJson($json);
        $serviceProvider = new ServiceProvider($spEntityId, $mdui);
        $serviceProvider->organizationEn = new Organization('Name', 'Organization Name', 'https://test.example.org');
        $serviceProvider->nameIdFormat = NameIdFormat::TRANSIENT_IDENTIFIER;
        $serviceProvider->supportUrlNl = 'https://my-test-sp.test/help-nl';
        $serviceProvider->supportUrlEn = 'https://my-test-sp.test/help-en';
        $serviceProvider->supportUrlPt = 'https://my-test-sp.test/help-pt';
        $serviceProvider->contactPersons = [
            $technicalContact,
            $firstSupportContact,
            $secondSupportContact,
        ];

        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->getContainer()->getParameter('api.users.profile.username'),
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $this->addServiceProviderFixture($serviceProvider);
        $this->addConsentFixture($userId, $spEntityId, $attributeHash, $consentType, $consentDate, $deletedAt);

        $client->request('GET', 'https://engine-api.dev.openconext.local/consent/' . $userId);

        $this->assertStatusCode(Response::HTTP_OK, $client);
        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');

        $expectedData = [
            [
                'service_provider' => [
                    'entity_id' => $spEntityId,
                    'display_name' => [
                        'en' => $serviceProvider->getMdui()->getDisplayNameOrNull('en'),
                        'nl' => $serviceProvider->getMdui()->getDisplayNameOrNull('nl'),
                        'pt' => $serviceProvider->getMdui()->getDisplayNameOrNull('pt'),
                    ],
                    'support_url' => [
                        'en' => $serviceProvider->supportUrlEn,
                        'nl' => $serviceProvider->supportUrlNl,
                        'pt' => $serviceProvider->supportUrlPt,
                    ],
                    'eula_url' => $serviceProvider->getCoins()->termsOfServiceUrl(),
                    'support_email' => $firstSupportContact->emailAddress,
                    'name_id_format' => $serviceProvider->nameIdFormat,
                    'organization_display_name' => [
                        'en' => $serviceProvider->organizationEn->displayName,
                        'nl' => $serviceProvider->organizationEn->displayName,
                        'pt' => $serviceProvider->organizationEn->displayName,
                    ],
                ],
                'consent_type' => $consentType,
                'consent_given_on' => (new DateTime($consentDate))->format(DATE_ATOM),
            ]
        ];
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals($expectedData, $responseData);
    }

    /**
     * @test
     * @group Api
     * @group Consent
     * @group Profile
     */
    public function consent_is_soft_deleted_from_the_consent_api()
    {
        $userId = 'urn:collab:person:institution-a:my-name-id';
        $spEntityId = 'https://my-test-sp.test';
        $attributeHash = 'abe55dff15fe253d91220e945cd0f2c5f4727430';
        $consentType = 'explicit';
        $consentDate = '2022-04-26 13:37:00';
        $deletedAt = '0000-00-00 00:00:00';

        $technicalContact = new ContactPerson('technical');
        $technicalContact->emailAddress = 'technical@my-test-sp.test';
        $firstSupportContact = new ContactPerson('support');
        $firstSupportContact->emailAddress = 'first-support@my-test-sp.test';
        $secondSupportContact = new ContactPerson('support');
        $secondSupportContact->emailAddress = 'second-support@my-test-sp.test';

        $serviceProvider = new ServiceProvider($spEntityId);
        $serviceProvider->displayNameEn = 'My Test SP';
        $serviceProvider->displayNameNl = 'Mijn Test SP';
        $serviceProvider->displayNamePt = 'O Meu teste SP';
        $serviceProvider->organizationEn = new Organization('Name', 'Organization Name', 'https://test.example.org');
        $serviceProvider->nameIdFormat = NameIdFormat::TRANSIENT_IDENTIFIER;
        $serviceProvider->supportUrlNl = 'https://my-test-sp.test/help-nl';
        $serviceProvider->supportUrlEn = 'https://my-test-sp.test/help-en';
        $serviceProvider->supportUrlPt = 'https://my-test-sp.test/help-pt';
        $serviceProvider->contactPersons = [
            $technicalContact,
            $firstSupportContact,
            $secondSupportContact,
        ];

        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->getContainer()->getParameter('api.users.profile.username'),
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $this->addServiceProviderFixture($serviceProvider);
        $this->addConsentFixture($userId, $spEntityId, $attributeHash, $consentType, $consentDate, $deletedAt);

        $data = json_encode(['collabPersonId' => $userId, 'serviceProviderEntityId' => $spEntityId]);
        $client->request('POST', 'https://engine-api.dev.openconext.local/remove-consent', [], [], [], $data);
        $this->assertStatusCode(Response::HTTP_OK, $client);
        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
        $dbResults = $this->findConsentByUserIdAndSPEntityId($userId, $spEntityId);
        // The consent row has been soft deleted, as proven by the deleted_at having a date time value
        $this->assertCount(1, $dbResults);
        $this->assertNotEquals('0000-00-00 00:00:00', $dbResults[0]['deleted_at']);
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $dbResults[0]['deleted_at']);
        $this->assertInstanceOf(DateTime::class, $dateTime);
        $this->assertNotNull($dbResults[0]['deleted_at']);
    }

    /**
     * @test
     * @group Api
     * @group Consent
     * @group Profile
     */
    public function consent_is_soft_deleted_from_the_consent_api_multiple_soft_deletions()
    {
        $userId = 'urn:collab:person:institution-a:my-name-id';
        $spEntityId = 'https://my-test-sp.test';
        $attributeHash = 'abe55dff15fe253d91220e945cd0f2c5f4727430';
        $consentType = 'explicit';
        $consentDate = '2022-04-26 13:37:00';
        $deletedAt = '0000-00-00 00:00:00';

        $technicalContact = new ContactPerson('technical');
        $technicalContact->emailAddress = 'technical@my-test-sp.test';
        $firstSupportContact = new ContactPerson('support');
        $firstSupportContact->emailAddress = 'first-support@my-test-sp.test';
        $secondSupportContact = new ContactPerson('support');
        $secondSupportContact->emailAddress = 'second-support@my-test-sp.test';

        $serviceProvider = new ServiceProvider($spEntityId);
        $serviceProvider->displayNameEn = 'My Test SP';
        $serviceProvider->displayNameNl = 'Mijn Test SP';
        $serviceProvider->displayNamePt = 'O Meu teste SP';
        $serviceProvider->organizationEn = new Organization('Name', 'Organization Name', 'https://test.example.org');
        $serviceProvider->nameIdFormat = NameIdFormat::TRANSIENT_IDENTIFIER;
        $serviceProvider->supportUrlNl = 'https://my-test-sp.test/help-nl';
        $serviceProvider->supportUrlEn = 'https://my-test-sp.test/help-en';
        $serviceProvider->supportUrlPt = 'https://my-test-sp.test/help-pt';
        $serviceProvider->contactPersons = [
            $technicalContact,
            $firstSupportContact,
            $secondSupportContact,
        ];

        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->getContainer()->getParameter('api.users.profile.username'),
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $this->addServiceProviderFixture($serviceProvider);
        $this->addConsentFixture($userId, $spEntityId, $attributeHash, $consentType, $consentDate, $deletedAt);
        $this->addConsentFixture($userId, $spEntityId, $attributeHash, $consentType, '2021-09-21 13:37:00', '2021-10-21 13:37:00');
        $this->addConsentFixture($userId, $spEntityId, $attributeHash, $consentType, '2021-09-21 13:37:00', '2020-10-21 13:37:00');

        $dbResults = $this->findConsentByUserIdAndSPEntityId($userId, $spEntityId);
        // Three consent rows are retrieved
        $this->assertCount(3, $dbResults);
        $count = $this->countConsentRemovals($dbResults);
        $this->assertEquals(1, $count['active']);
        $this->assertEquals(2, $count['removed']);
        $data = json_encode(['collabPersonId' => $userId, 'serviceProviderEntityId' => $spEntityId]);
        $client->request('POST', 'https://engine-api.dev.openconext.local/remove-consent', [], [], [], $data);
        $this->assertStatusCode(Response::HTTP_OK, $client);
        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
        $dbResults = $this->findConsentByUserIdAndSPEntityId($userId, $spEntityId);
        // Three consent rows are retrieved
        $this->assertCount(3, $dbResults);
        $count = $this->countConsentRemovals($dbResults);
        $this->assertEquals(0, $count['active']);
        $this->assertEquals(3, $count['removed']);
    }

    /**
     * @test
     * @group Api
     * @group Consent
     * @group FeatureToggle
     */
    public function cannot_access_the_remove_consent_api_if_the_feature_has_been_disabled()
    {
        $collabPersonId = 'urn:collab:person:test';

        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->getContainer()->getParameter('api.users.deprovision.username'),
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('api.users.deprovision.password'),
        ]);

        $this->disableRemoveConsentApiFeatureFor($client);

        $data = json_encode(['collabPersonId' => $collabPersonId, 'serviceProviderEntityId' => 'https://example.com/metadata']);
        $client->request('POST', 'https://engine-api.dev.openconext.local/remove-consent', [], [], [], $data);

        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $client);
        $this->assertResponseIsJson($client);
    }

    /**
     * @param Client $client
     */
    private function disableRemoveConsentApiFeatureFor(Client $client)
    {
        $featureToggles = new FeatureConfiguration([
            'api.consent_remove' => new Feature('api.consent_remove', false),
            'eb.feature_enable_consent' => new Feature('eb.feature_enable_consent', true),
        ]);
        $client->getContainer()->set('engineblock.features', $featureToggles);
    }

    /**
     * @test
     * @group Api
     * @group Consent
     */
    public function cannot_access_the_remove_consent_api_if_user_does_not_have_profile_role()
    {
        $collabPersonId = 'urn:collab:person:test';

        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'no_roles',
            'PHP_AUTH_PW' => 'no_roles',
        ]);

        $data = json_encode(['collabPersonId' => $collabPersonId, 'serviceProviderEntityId' => 'https://example.com/metadata']);
        $client->request('POST', 'https://engine-api.dev.openconext.local/remove-consent', [], [], [], $data);

        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $client);
        $this->assertResponseIsJson($client);
    }

    /**
     * @test
     * @group Api
     * @group Consent
     */
    public function no_consent_is_removed_if_request_parameters_are_missing_or_incorrect()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->getContainer()->getParameter('api.users.profile.username'),
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $data = json_encode(['userId' => 'urn:collab:person:test', 'serviceProviderId' => 'https://example.com/metadata']);
        $client->request('POST', 'https://engine-api.dev.openconext.local/remove-consent', [], [], [], $data);

        $this->assertStatusCode(Response::HTTP_FOUND, $client);
        $this->assertResponseIsJson($client);
    }

    /**
     * @test
     * @group Api
     * @group Consent
     */
    public function no_consent_is_removed_if_collab_person_id_is_unknown()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->getContainer()->getParameter('api.users.profile.username'),
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $data = json_encode(['collabPersonId' => 'urn:collab:person:test', 'serviceProviderEntityId' => 'https://example.com/metadata']);
        $client->request('POST', 'https://engine-api.dev.openconext.local/remove-consent', [], [], [], $data);

        $this->assertStatusCode(Response::HTTP_OK, $client);
        $this->assertResponseIsJson($client);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame(false, $responseData);
    }

    public function invalidHttpMethodProvider()
    {
        return [
            'POST' => ['POST'],
            'DELETE' => ['DELETE'],
            'HEAD' => ['HEAD'],
            'PUT' => ['PUT'],
            'OPTIONS' => ['OPTIONS']
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

    private function disableConsentApiFeatureFor(Client $client)
    {
        $featureToggles = new FeatureConfiguration([
            'api.consent_listing' => new Feature('api.consent_listing', false),
            'eb.feature_enable_consent' => new Feature('eb.feature_enable_consent', false),
        ]);
        $container = $client->getContainer();
        $container->set('engineblock.features', $featureToggles);
    }

    private function disableEngineConsentFeatureFor(Client $client)
    {
        $featureToggles = new FeatureConfiguration([
            'eb.feature_enable_consent' => new Feature('eb.feature_enable_consent', false)
        ]);
        $container = $client->getContainer();
        $container->set('engineblock.features', $featureToggles);
    }

    /**
     * @test
     * @group Api
     * @group Consent
     * @group FeatureToggle
     */
    public function cannot_access_the_consent_post_api_if_the_engineblock_consent_feature_has_been_disabled()
    {
        $collabPersonId = 'urn:collab:person:test';

        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->getContainer()->getParameter('api.users.deprovision.username'),
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('api.users.deprovision.password'),
        ]);

        $this->disableEngineConsentFeatureFor($client);

        $data = json_encode(['collabPersonId' => $collabPersonId, 'serviceProviderEntityId' => 'https://example.com/metadata']);
        $client->request('POST', 'https://engine-api.dev.openconext.local/remove-consent', [], [], [], $data);

        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $client);
        $this->assertResponseIsJson($client);
    }

    /**
     * @test
     * @group Api
     * @group Consent
     * @group Profile
     * @group FeatureToggle
     */
    public function cannot_access_the_consent_get_api_if_the_engineblock_consent_feature_has_been_disabled()
    {
        $userId = 'my-name-id';

        $client = $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->getContainer()->getParameter('api.users.profile.username'),
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $this->disableEngineConsentFeatureFor($client);

        $client->request('GET', 'https://engine-api.dev.openconext.local/consent/' . $userId);
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    private function addConsentFixture($userId, $serviceId, $attributeHash, $consentType, $consentDate, $deletedAt)
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
                'deleted_at'   => ':deleted_at',
            ])
            ->setParameters([
                ':user_id'      => sha1($userId),
                ':service_id'   => $serviceId,
                ':attribute'    => $attributeHash,
                ':consent_type' => $consentType,
                ':consent_date' => $consentDate,
                ':deleted_at' => $deletedAt,
            ])
            ->execute();
    }

    private function findConsentByUserIdAndSPEntityId(string $collabPersonId, string $spEntityId): array
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $this->getContainer()->get('doctrine')->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select('*')
            ->from('consent')
            ->where('hashed_user_id = :userId')
            ->andWhere('service_id = :serviceId')
            ->setParameters([
                ':userId' => sha1($collabPersonId),
                ':serviceId' => $spEntityId
            ]);

        /** @var PDOStatement $statement */
        $statement = $queryBuilder->execute();
        return (array) $statement->fetchAll();
    }

    private function addServiceProviderFixture(ServiceProvider $serviceProvider)
    {
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $em->persist($serviceProvider);
        $em->flush();
    }

    private function clearMetadataFixtures()
    {
        $queryBuilder = $this->getContainer()->get('doctrine')->getConnection()->createQueryBuilder();
        $queryBuilder
            ->delete('sso_provider_roles_eb5')
            ->execute();
    }

    private function clearConsentFixtures()
    {
        $queryBuilder = $this->getContainer()->get('doctrine')->getConnection()->createQueryBuilder();
        $queryBuilder
            ->delete('consent')
            ->execute();
    }

    private function countConsentRemovals(array $dbResults): array
    {
        $removedCount = 0;
        $activeCount = 0;
        foreach ($dbResults as $consentRow) {
            if ($consentRow['deleted_at'] === '0000-00-00 00:00:00') {
                $activeCount++;
            } else {
                $removedCount++;
            }
        }
        return [
            'removed' => $removedCount,
            'active' => $activeCount,
        ];
    }

    private function assertResponseIsJson(Client $client)
    {
        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }
}
