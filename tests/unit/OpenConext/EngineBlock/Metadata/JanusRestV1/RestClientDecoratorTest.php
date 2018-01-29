<?php

namespace OpenConext\EngineBlock\Metadata\JanusRestV1;

use Mockery;
use PHPUnit_Framework_TestCase;

/**
 * Class RestClientDecoratorTest
 * @package OpenConext\EngineBlock\Metadata\JanusRestV1
 */
class RestClientDecoratorTest extends PHPUnit_Framework_TestCase
{
    public function testFindEntity()
    {
        $sp = array('EntityID' => 'https://sp.example.edu');
        $idp = array('EntityID' => 'https://idp.example.edu');

        $mockClient = Mockery::mock('OpenConext\EngineBlock\Metadata\JanusRestV1\RestClientInterface');
        $mockClient->shouldReceive('getIdpList')->andReturn(array($idp['EntityID'] => $idp));
        $mockClient->shouldReceive('getSpList')->andReturn(array($sp['EntityID'] => $sp));

        $decorated = new RestClientDecorator($mockClient);
        
        $this->assertEquals($idp, $decorated->findIdentityProviderMetadataByEntityId($idp['EntityID']));
        $this->assertEquals($sp, $decorated->findServiceProviderMetadataByEntityId($sp['EntityID']));
        $this->assertEquals($sp, $decorated->findMetadataByEntityId($sp['EntityID']));
        $this->assertEquals($idp, $decorated->findMetadataByEntityId($idp['EntityID']));
        $this->assertNull($decorated->findMetadataByEntityId('https://404.example.edu'));
    }
}
