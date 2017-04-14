<?php

namespace OpenConext\EngineBlockBundle\Tests;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AttributeReleasePolicyControllerTest extends WebTestCase
{
    /**
     * @test
     * @group Api
     * @group Arp
     * @group Profile
     */
    public function authentication_is_required_for_applying_arps()
    {
        $unauthenticatedClient = $this->makeClient();
        $unauthenticatedClient->request('POST', 'https://engine-api.vm.openconext.org/arp');
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED,  $unauthenticatedClient);
    }

    /**
     * @test
     * @group Api
     * @group Arp
     * @group Profile
     *
     * @dataProvider invalidHttpMethodProvider
     * @param string $invalidHttpMethod
     */
    public function only_post_requests_are_allowed_when_pushing_metadata($invalidHttpMethod)
    {
        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.profile.username'),
            'password' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $client->request($invalidHttpMethod, 'https://engine-api.vm.openconext.org/arp');
        $this->assertStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Arp
     * @group Profile
     */
    public function cannot_push_metadata_if_user_does_not_have_profile_role()
    {
        $client = $this->makeClient([
            'username' => 'no_roles',
            'password' => 'no_roles',
        ]);

        $client->request('POST', 'https://engine-api.vm.openconext.org/arp');
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Arp
     * @group Profile
     *
     * @dataProvider invalidJsonPayloadProvider
     * @param string $invalidJsonPayload
     */
    public function cannot_push_invalid_content_to_the_metadata_push_api($invalidJsonPayload)
    {
        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.profile.username'),
            'password' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $client->request(
            'POST',
            'https://engine-api.vm.openconext.org/arp',
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
            'string body' => ['"not-an-object"'],
            'integer body' => ['123'],
            'empty object body' => ['{}'],
            'entityIds: string' => [json_encode([
                'entityIds' => 'not-an-object',
                'attributes'  => [],
            ])],
            'entityIds: integer' => [json_encode([
                'entityIds' => 123,
                'attributes'  => [],
            ])],
            'entityIds: null' => [json_encode([
                'entityIds' => null,
                'attributes'  => [],
            ])],
            'entityIds: empty array' => [json_encode([
                'entityIds' => [],
                'attributes'  => [],
            ])],
            'attributes: string' => [json_encode([
                'entityIds'  => ['https://test.idp.test'],
                'attributes' => 'not-an-object',
            ])],
            'attributes: integer' => [json_encode([
                'entityIds'  => ['https://test.idp.test'],
                'attributes' => 123,
            ])],
            'attributes: null' => [json_encode([
                'entityIds'  => ['https://test.idp.test'],
                'attributes' => null,
            ])],
            'attributes: non-string key' => [json_encode([
                'entityIds'  => ['https://test.idp.test'],
                'attributes' => [1 => ['attribute-value']],
            ])],
            'attributes: string value (non-array)' => [json_encode([
                'entityIds'  => ['https://test.idp.test'],
                'attributes' => ['attribute-key' => 'attribute-value'],
            ])],
            'attributes: integer value (non-array)' => [json_encode([
                'entityIds'  => ['https://test.idp.test'],
                'attributes' => ['attribute-key' => 1],
            ])],
        ];
    }

}
