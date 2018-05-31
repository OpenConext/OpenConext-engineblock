<?php

namespace OpenConext\EngineBlockBundle\Tests;

use DateTime;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use OpenConext\EngineBlockBundle\Configuration\Feature;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

final class DeprovisionControllerTest extends WebTestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->clearConsentFixtures();

        parent::__construct($name, $data, $dataName);

    }

    public function tearDown()
    {
        $this->clearConsentFixtures();
    }

    /**
     * @test
     * @group Api
     * @group Deprovision
     */
    public function authentication_is_required_for_accessing_the_deprovision_api()
    {
        $collabPersonId = 'my-name-id';

        $unauthenticatedClient = $this->makeClient();
        $unauthenticatedClient->request('GET', 'https://engine-api.vm.openconext.org/deprovision/' . $collabPersonId);
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED,  $unauthenticatedClient);

        $unauthenticatedClient = $this->makeClient();
        $unauthenticatedClient->request('DELETE', 'https://engine-api.vm.openconext.org/deprovision/' . $collabPersonId);
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED,  $unauthenticatedClient);

        $unauthenticatedClient = $this->makeClient();
        $unauthenticatedClient->request('DELETE', 'https://engine-api.vm.openconext.org/deprovision/' . $collabPersonId . '/dry-run');
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED,  $unauthenticatedClient);
    }

    /**
     * @test
     * @group Api
     * @group Deprovision
     */
    public function only_get_or_delete_requests_are_allowed_when_accessing_the_deprovision_api()
    {
        $collabPersonId = 'my-name-id';

        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.deprovision.username'),
            'password' => $this->getContainer()->getParameter('api.users.deprovision.password'),
        ]);

        $client->request('PUT', 'https://engine-api.vm.openconext.org/deprovision/' . $collabPersonId);
        $this->assertStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, $client);

        $client->request('HEAD', 'https://engine-api.vm.openconext.org/deprovision/' . $collabPersonId);
        $this->assertStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, $client);

        $client->request('GET', 'https://engine-api.vm.openconext.org/deprovision/' . $collabPersonId . '/dry-run');
        $this->assertStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Deprovision
     * @group FeatureToggle
     */
    public function cannot_access_the_deprovision_api_if_the_feature_has_been_disabled()
    {
        $collabPersonId = 'my-name-id';

        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.deprovision.username'),
            'password' => $this->getContainer()->getParameter('api.users.deprovision.password'),
        ]);

        $this->disableDeprovisionApiFeatureFor($client);

        $client->request('GET', 'https://engine-api.vm.openconext.org/deprovision/' . $collabPersonId);
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Deprovision
     */
    public function cannot_access_the_deprovision_api_if_user_does_not_have_deprovision_role()
    {
        $collabPersonId = 'my-name-id';

        $client = $this->makeClient([
            'username' => 'no_roles',
            'password' => 'no_roles',
        ]);

        $this->enableDeprovisionApiFeatureFor($client);

        $client->request('GET', 'https://engine-api.vm.openconext.org/deprovision/' . $collabPersonId);

        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
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
        $collabPersonId = 'my-name-id';

        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.deprovision.username'),
            'password' => $this->getContainer()->getParameter('api.users.deprovision.password'),
        ]);

        $this->enableDeprovisionApiFeatureFor($client);

        $client->request($method, 'https://engine-api.vm.openconext.org/' . trim($path, '/'));

        $this->assertStatusCode(Response::HTTP_OK, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');

        $expectedData = [
            'status' => 'OK',
            'name'   => 'EngineBlock',
            'data'   => [],
        ];

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame($expectedData, $responseData);
    }

    /**
     * @test
     * @group Api
     * @group Deprovision
     *
     * @dataProvider provideDeprovisionEndPoints
     */
    public function all_user_data_is_returned_for_collab_person_id($method, $path)
    {
        $spEntityId = 'https://my-test-sp.test';
        $attributeHash = 'abe55dff15fe253d91220e945cd0f2c5f4727430';
        $consentType = 'explicit';
        $consentDate = '2017-04-18 13:37:00';

        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.deprovision.username'),
            'password' => $this->getContainer()->getParameter('api.users.deprovision.password'),
        ]);

        $this->addConsentFixture($userId, $spEntityId, $attributeHash, $consentType, $consentDate);
        $this->enableDeprovisionApiFeatureFor($client);

        $client->request($method, 'https://engine-api.vm.openconext.org/' . trim($path, '/'));

        $this->assertStatusCode(Response::HTTP_OK, $client);
        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');

        $expectedData = [
            'status' => 'OK',
            'name'   => 'EngineBlock',
            'data' => [],
        ];

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals($expectedData, $responseData);
    }

    public function provideDeprovisionEndpoints()
    {
        return [
            ['GET', '/deprovision/my-name-id'],
            ['DELETE', '/deprovision/my-name-id'],
            ['DELETE', '/deprovision/my-name-id/dry-run'],
        ];
    }

    private function enableDeprovisionApiFeatureFor(Client $client)
    {
        $featureToggles = new FeatureConfiguration([
            'api.deprovision' => new Feature('api.deprovision', true)
        ]);
        $client->getContainer()->set('engineblock.features', $featureToggles);
    }

    private function disableDeprovisionApiFeatureFor(Client $client)
    {
        $featureToggles = new FeatureConfiguration([
            'api.deprovision' => new Feature('api.deprovision', false)
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

    private function clearConsentFixtures()
    {
        $queryBuilder = $this->getContainer()->get('doctrine')->getConnection()->createQueryBuilder();
        $queryBuilder
            ->delete('consent')
            ->execute();
    }
}
