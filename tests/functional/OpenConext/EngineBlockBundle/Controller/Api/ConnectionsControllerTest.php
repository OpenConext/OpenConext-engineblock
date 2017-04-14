<?php

namespace OpenConext\EngineBlockBundle\Tests;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ConnectionsControllerTest extends WebTestCase
{
    /**
     * @test
     * @group Api
     * @group Connections
     * @group Janus
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
     * @group Janus
     *
     * @dataProvider invalidHttpMethodProvider
     * @param string $invalidHttpMethod
     */
    public function only_post_requests_are_allowed_when_pushing_metadata($invalidHttpMethod)
    {
        $client = $this->makeClient([
            'username' => $this->getContainer()->getParameter('api.users.janus.username'),
            'password' => $this->getContainer()->getParameter('api.users.janus.password'),
        ]);

        $client->request($invalidHttpMethod, 'https://engine-api.vm.openconext.org/api/connections');
        $this->assertStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, $client);

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
}
