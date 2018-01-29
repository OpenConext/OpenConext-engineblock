<?php

namespace OpenConext\EngineBlock\Metadata\JanusRestV1;

use PHPUnit_Framework_TestCase;

/**
 * Class CacheProxyClientTest
 * @package OpenConext\EngineBlock\Metadata\JanusRestV1
 */
class CacheProxyClientTest extends PHPUnit_Framework_TestCase
{
    public function testCachingIdpList()
    {
        $mockClient = \Mockery::mock('OpenConext\EngineBlock\Metadata\JanusRestV1\RestClientInterface');

        $mockClient
            ->shouldReceive('getIdpList')
            ->andReturn(array())
            ->once();

        $cacheProxy = new CacheProxyClient($mockClient);
        $cacheProxy->getIdpList();
        $cacheProxy->getIdpList();
        $cacheProxy->getIdpList();
    }

    public function testCachingSpList()
    {
        $mockClient = \Mockery::mock('OpenConext\EngineBlock\Metadata\JanusRestV1\RestClientInterface');

        $mockClient
            ->shouldReceive('getSpList')
            ->andReturn(array())
            ->once();

        $cacheProxy = new CacheProxyClient($mockClient);
        $cacheProxy->getSpList();
        $cacheProxy->getSpList();
        $cacheProxy->getSpList();
    }

    public function testCachingGetEntity()
    {
        $mockClient = \Mockery::mock('OpenConext\EngineBlock\Metadata\JanusRestV1\RestClientInterface');

        $sp1 = array('EntityID' => 'https://sp2.example.edu');
        $mockClient
            ->shouldReceive('getEntity')
            ->with('https://sp1.example.edu')
            ->andReturn($sp1)
            ->once();

        $sp2 = array('EntityID' => 'https://sp2.example.edu');
        $mockClient
            ->shouldReceive('getEntity')
            ->with('https://sp2.example.edu')
            ->andReturn($sp2)
            ->once();

        $cacheProxy = new CacheProxyClient($mockClient);
        $this->assertEquals($sp1, $cacheProxy->getEntity('https://sp1.example.edu'));
        $this->assertEquals($sp1, $cacheProxy->getEntity('https://sp1.example.edu'));
        $this->assertEquals($sp2, $cacheProxy->getEntity('https://sp2.example.edu'));
        $this->assertEquals($sp2, $cacheProxy->getEntity('https://sp2.example.edu'));
    }
}
