<?php

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

final class HeartbeatController extends WebTestCase
{
    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * @test
     * @group Api
     */
    public function engineblock_has_a_heartbeat()
    {
        $this->client->request('GET', 'https://engine-api.vm.openconext.org/');
        $this->assertStatusCode(200, $this->client);
    }
}
