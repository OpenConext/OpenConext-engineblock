<?php

namespace OpenConext\EngineBlockBundle\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use OpenConext\EngineBlockBundle\Configuration\Feature;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;
use PDO;

class ConnectionsControllerTest extends WebTestCase
{
    /**
     * @test
     * @group Api
     * @group Connections
     * @group MetadataPush
     */
    public function authentication_is_required_for_pushing_metadata()
    {
        $unauthenticatedClient = $this->makeClient();
        $unauthenticatedClient->request('POST', 'https://engine-api.vm.openconext.org/api/connections');
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED,  $unauthenticatedClient);
    }

    /**
     * @test
     * @group Api
     * @group Connections
     * @group MetadataPush
     *
     * @dataProvider invalidHttpMethodProvider
     * @param string $invalidHttpMethod
     */
    public function only_post_requests_are_allowed_when_pushing_metadata($invalidHttpMethod)
    {
        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.metadataPush.username'),
            'password' => $this->getContainer()->getParameter('api.users.metadataPush.password'),
        ]);

        $client->request($invalidHttpMethod, 'https://engine-api.vm.openconext.org/api/connections');
        $this->assertStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Connections
     * @group MetadataPush
     * @group FeatureToggle
     */
    public function cannot_push_metadata_if_feature_is_disabled()
    {
        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.metadataPush.username'),
            'password' => $this->getContainer()->getParameter('api.users.metadataPush.password'),
        ]);

        $this->disableMetadataPushApiFeatureFor($client);

        $client->request('POST', 'https://engine-api.vm.openconext.org/api/connections');
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Connections
     * @group MetadataPush
     */
    public function cannot_push_metadata_if_user_does_not_have_manage_role()
    {
        $client = $this->makeClient([
            'username' => 'no_roles',
            'password' => 'no_roles',
        ]);

        $this->enableMetadataPushApiFeatureFor($client);

        $client->request('POST', 'https://engine-api.vm.openconext.org/api/connections');
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Connections
     * @group MetadataPush
     *
     * @dataProvider invalidJsonPayloadProvider
     * @param string $invalidJsonPayload
     */
    public function cannot_push_invalid_content_to_the_metadata_push_api($invalidJsonPayload)
    {
        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.metadataPush.username'),
            'password' => $this->getContainer()->getParameter('api.users.metadataPush.password'),
        ]);

        $this->enableMetadataPushApiFeatureFor($client);

        $client->request(
            'POST',
            'https://engine-api.vm.openconext.org/api/connections',
            [],
            [],
            [],
            $invalidJsonPayload
        );
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Connections
     * @group MetadataPush
     *
     */
    public function pushing_data_to_engineblock_should_succeed()
    {
        $this->clearMetadataFixtures();

        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.metadataPush.username'),
            'password' => $this->getContainer()->getParameter('api.users.metadataPush.password'),
        ]);

        $this->enableMetadataPushApiFeatureFor($client);

        foreach ($this->validConnectionsData() as $step) {

            $payload = $this->createJsonData($step);

            $client->request(
                'POST',
                'https://engine-api.vm.openconext.org/api/connections',
                [],
                [],
                [],
                $payload
            );
            $this->assertStatusCode(Response::HTTP_OK, $client);

            // check content type
            $isContentTypeJson = $client->getResponse()->headers->contains('Content-Type', 'application/json');
            $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');

            // check response status
            $body = $client->getResponse()->getContent();
            $result = json_decode($body, true);
            $this->assertTrue($result['success']);

            // validate data
            $metadata = $this->getStoredMetadata();
            foreach ($step as $role) {
                $this->assertArrayHasKey($role['entityId'], $metadata);

                $data = $metadata[$role['entityId']];
                $this->assertSame($role['entityId'], $data['entity_id']);
                $this->assertSame($role['name'], $data['name_en']);
                $this->assertSame($role['type'], 'saml20-'.$data['type']);

                unset($metadata[$role['entityId']]);
            }

            $this->assertEmpty($metadata);
        }
    }


    public function invalidHttpMethodProvider()
    {
        return [
            'GET' => ['GET'],
            'DELETE' => ['DELETE'],
            'HEAD' => ['HEAD'],
            'PUT' => ['PUT'],
            'OPTIONS' => ['OPTIONS']
        ];
    }

    public function invalidJsonPayloadProvider()
    {
        return [
            'string body' => ['"a-string"'],
            'integer body' => ['123'],
            'array body' => ['["an-array"]'],
            'empty object body' => ['{}'],
            'string connections' => ['{connections: "a-string"}'],
            'integer connections' => ['{connections: 1}'],
            'array connections' => ['{connections: ["a", "b", "c"]'],
        ];
    }

    private function enableMetadataPushApiFeatureFor(Client $client)
    {
        $featureToggles = new FeatureConfiguration([
            'api.metadata_push' => new Feature('api.metadata_push', true)
        ]);
        $client->getContainer()->set('engineblock.features', $featureToggles);
    }

    private function disableMetadataPushApiFeatureFor(Client $client)
    {
        $featureToggles = new FeatureConfiguration([
            'api.metadata_push' => new Feature('api.metadata_push', false)
        ]);
        $client->getContainer()->set('engineblock.features', $featureToggles);
    }

    private function clearMetadataFixtures()
    {
        $queryBuilder = $this->getContainer()->get('doctrine')->getConnection()->createQueryBuilder();
        $queryBuilder
            ->delete('sso_provider_roles_eb5')
            ->execute();
    }

    private function getStoredMetadata()
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getContainer()->get('doctrine')->getConnection()->createQueryBuilder();
        $metadata = $queryBuilder
            ->select('r.entity_id, r.name_en, r.type')
            ->from('sso_provider_roles_eb5', 'r')
            ->execute()
            ->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        foreach ($metadata as $role) {
            $results[$role['entity_id']] = $role;
        }
        return $results;
    }

    private function createJsonData($connections)
    {
        $connectionsJson = [];
        foreach ($connections as $data) {
            $connectionsJson[] = $this->createPayloadConnectionJson($data['uuid'], $data['entityId'], $data['name'], $data['type']);
        }
        $connectionsJson = implode(',', $connectionsJson);

        return sprintf('{"connections":{%s}}', $connectionsJson);
    }

    private function createPayloadConnectionJson($uuid, $entityId, $name, $type)
    {
        return sprintf('"%1$s":{
            "allow_all_entities":true,
            "allowed_connections":[],
            "metadata":{
                "name":{
                    "en":"%3$s"
                    }
                },
            "name":"%2$s",
            "state":"prodaccepted",
            "type":"%4$s"
        }',
            $uuid,
            $entityId,
            $name,
            $type);
    }

    private function validConnectionsData()
    {
        return [
            [
                [
                    'uuid' => '00000000-0000-0000-0000-000000000000',
                    'entityId' => 'https://my-idp.test/1',
                    'name' => 'SP0',
                    'type' => 'saml20-sp',
                ],
                [
                    'uuid' => '00000000-0000-0000-0000-000000000001',
                    'entityId' => 'https://my-sp.test/2',
                    'name' => 'SP1',
                    'type' => 'saml20-sp',
                ],[
                    'uuid' => '00000000-0000-0000-0000-000000000002',
                    'entityId' => 'https://my-idp.test/3',
                    'name' => 'IDP3',
                    'type' => 'saml20-idp',
                ],
            ],
            [
                [
                    'uuid' => '00000000-0000-0000-0000-000000000000',
                    'entityId' => 'https://my-idp.test/1',
                    'name' => 'SP0 updated',
                    'type' => 'saml20-sp',
                ],
                [
                    'uuid' => '00000000-0000-0000-0000-000000000001',
                    'entityId' => 'https://my-sp.test/2',
                    'name' => 'SP1 updated',
                    'type' => 'saml20-sp',
                ],[
                    'uuid' => '00000000-0000-0000-0000-000000000002',
                    'entityId' => 'https://my-idp.test/3',
                    'name' => 'IDP3 updated',
                    'type' => 'saml20-idp',
                ],
            ],
            [
                [
                    'uuid' => '00000000-0000-0000-0000-000000000001',
                    'entityId' => 'https://my-sp.test/2',
                    'name' => 'SP1',
                    'type' => 'saml20-sp',
                ],
            ],
        ];
    }
}
