<?php

namespace OpenConext\EngineBlockBundle\Tests;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class HeartbeatControllerTest extends WebTestCase
{
    /**
     * @test
     * @group Api
     */
    public function engineblock_has_a_heartbeat()
    {
        $client = $this->createClient();
        $client->request('GET', 'https://engine-api.vm.openconext.org/');
        $this->assertStatusCode(Response::HTTP_OK, $client);
    }
}
