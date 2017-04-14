<?php

namespace OpenConext\EngineBlockBundle\Tests;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use OpenConext\EngineBlockBundle\Configuration\Feature;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class MetadataControllerTest extends WebTestCase
{
    /**
     * @test
     * @group Api
     * @group Metadata
     * @group Profile
     *
     * @dataProvider invalidHttpMethodProvider
     * @param string $invalidHttpMethod
     */
    public function only_get_requests_are_allowed_when_pushing_metadata($invalidHttpMethod)
    {
        $entityId = 'https://test-idp.test';

        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.profile.username'),
            'password' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $client->request(
            $invalidHttpMethod,
            'https://engine-api.vm.openconext.org/metadata/idp?entity-id='.$entityId
        );
        $this->assertStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Metadata
     * @group Profile
     */
    public function authentication_is_required_for_getting_metadata_for_idp()
    {
        $entityId = 'https://test-idp.test';

        $unauthenticatedClient = $this->makeClient();
        $unauthenticatedClient->request(
            'GET',
            'https://engine-api.vm.openconext.org/metadata/idp?entity-id='.$entityId
        );
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED,  $unauthenticatedClient);
    }

    /**
     * @test
     * @group Api
     * @group Metadata
     * @group Profile
     * @group FeatureToggle
     */
    public function cannot_get_an_idps_metadata_if_the_feature_has_been_disabled()
    {
        $entityId = 'https://test-idp.test';

        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.profile.username'),
            'password' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $this->disableMetadataApiFeatureFor($client);

        $client->request(
            'GET',
            'https://engine-api.vm.openconext.org/metadata/idp?entity-id='.$entityId
        );
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Metadata
     * @group Profile
     */
    public function cannot_get_an_idps_metadata_if_user_does_not_have_profile_role()
    {
        $entityId = 'https://test-idp.test';

        $client = $this->makeClient([
            'username' => 'no_roles',
            'password' => 'no_roles',
        ]);

        $this->enableMetadataApiFeatureFor($client);

        $client->request(
            'GET',
            'https://engine-api.vm.openconext.org/metadata/idp?entity-id='.$entityId
        );
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Metadata
     * @group Profile
     *
     * @dataProvider invalidEntityIdProvider
     * @param string $invalidEntityId
     */
    public function cannot_get_metadata_for_an_idp_if_an_invalid_entity_id_has_been_given($invalidEntityId)
    {
        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.profile.username'),
            'password' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $this->enableMetadataApiFeatureFor($client);

        $client->request(
            'GET',
            'https://engine-api.vm.openconext.org/metadata/idp?entity-id='.$invalidEntityId,
            [],
            [],
            []
        );
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
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

    public function invalidEntityIdProvider()
    {
        return [
            'empty string' => [''],
            'null' => [null]
        ];
    }

    private function enableMetadataApiFeatureFor(Client $client)
    {
        $featureToggles = new FeatureConfiguration([
            'api.metadata_api' => new Feature('api.metadata_api', true)
        ]);
        $client->getContainer()->set('engineblock.features', $featureToggles);
    }

    private function disableMetadataApiFeatureFor(Client $client)
    {
        $featureToggles = new FeatureConfiguration([
            'api.metadata_api' => new Feature('api.metadata_api', false)
        ]);
        $client->getContainer()->set('engineblock.features', $featureToggles);
    }
}
