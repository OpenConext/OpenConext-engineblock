<?php

namespace OpenConext\EngineBlockBundle\Tests;

use DateTime;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlockBundle\Configuration\Feature;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use OpenConext\Value\Saml\NameIdFormat;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

final class ConsentControllerTest extends WebTestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->clearConsentFixtures();
        $this->clearMetadataFixtures();

        parent::__construct($name, $data, $dataName);

    }

    public function tearDown()
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

        $unauthenticatedClient = $this->makeClient();
        $unauthenticatedClient->request('GET', 'https://engine-api.vm.openconext.org/consent/' . $userId);
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

        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.profile.username'),
            'password' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $client->request($invalidHttpMethod, 'https://engine-api.vm.openconext.org/consent/' . $userId);
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

        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.profile.username'),
            'password' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $this->disableConsentApiFeatureFor($client);

        $client->request('GET', 'https://engine-api.vm.openconext.org/consent/' . $userId);
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

        $client = $this->makeClient([
            'username' => 'no_roles',
            'password' => 'no_roles',
        ]);

        $this->enableConsentApiFeatureFor($client);

        $client->request('GET', 'https://engine-api.vm.openconext.org/consent/' . $userId);

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

        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.profile.username'),
            'password' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $this->enableConsentApiFeatureFor($client);

        $client->request('GET', 'https://engine-api.vm.openconext.org/consent/' . $userId);

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

        $technicalContact = new ContactPerson('technical');
        $technicalContact->emailAddress = 'technical@my-test-sp.test';
        $firstSupportContact = new ContactPerson('support');
        $firstSupportContact->emailAddress = 'first-support@my-test-sp.test';
        $secondSupportContact = new ContactPerson('support');
        $secondSupportContact->emailAddress = 'second-support@my-test-sp.test';

        $serviceProvider = new ServiceProvider($spEntityId);
        $serviceProvider->displayNameEn = 'My Test SP';
        $serviceProvider->displayNameNl = 'Mijn Test SP';
        $serviceProvider->nameIdFormat = NameIdFormat::TRANSIENT_IDENTIFIER;
        $serviceProvider->supportUrlNl = 'https://my-test-sp.test/help-nl';
        $serviceProvider->supportUrlEn = 'https://my-test-sp.test/help-en';
        $serviceProvider->contactPersons = [
            $technicalContact,
            $firstSupportContact,
            $secondSupportContact,
        ];

        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.profile.username'),
            'password' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $this->addServiceProviderFixture($serviceProvider);
        $this->addConsentFixture($userId, $spEntityId, $attributeHash, $consentType, $consentDate);
        $this->enableConsentApiFeatureFor($client);

        $client->request('GET', 'https://engine-api.vm.openconext.org/consent/' . $userId);

        $this->assertStatusCode(Response::HTTP_OK, $client);
        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');

        $expectedData = [
            [
                'service_provider' => [
                    'entity_id' => $spEntityId,
                    'display_name' => [
                        'en' => $serviceProvider->displayNameEn,
                        'nl' => $serviceProvider->displayNameNl,
                    ],
                    'support_url' => [
                        'en' => $serviceProvider->supportUrlEn,
                        'nl' => $serviceProvider->supportUrlNl,
                    ],
                    'eula_url' => $serviceProvider->getCoins()->termsOfServiceUrl(),
                    'support_email' => $firstSupportContact->emailAddress,
                    'name_id_format' => $serviceProvider->nameIdFormat,
                ],
                'consent_type' => $consentType,
                'consent_given_on' => (new DateTime($consentDate))->format(DATE_ATOM),
            ]
        ];
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals($expectedData, $responseData);
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

    private function enableConsentApiFeatureFor(Client $client)
    {
        $featureToggles = new FeatureConfiguration([
            'api.consent_listing' => new Feature('api.consent_listing', true)
        ]);
        $client->getContainer()->set('engineblock.features', $featureToggles);
    }

    private function disableConsentApiFeatureFor(Client $client)
    {
        $featureToggles = new FeatureConfiguration([
            'api.consent_listing' => new Feature('api.consent_listing', false)
        ]);
        $client->getContainer()->set('engineblock.features', $featureToggles);
    }

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
}
