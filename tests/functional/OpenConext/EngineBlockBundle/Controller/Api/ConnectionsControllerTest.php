<?php

namespace OpenConext\EngineBlockBundle\Tests;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use OpenConext\EngineBlockBundle\Configuration\Feature;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

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
}
