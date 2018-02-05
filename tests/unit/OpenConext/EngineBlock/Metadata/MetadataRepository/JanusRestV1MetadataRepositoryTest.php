<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository;

use Mockery;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use PHPUnit_Framework_TestCase;

/**
 * Class JanusRestV1MetadataRepositoryTest
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository
 */
class JanusRestV1MetadataRepositoryTest extends PHPUnit_Framework_TestCase
{
    public function testFindIdentityProviders()
    {
        $mockRestClient = Mockery::mock(
            'OpenConext\EngineBlock\Metadata\JanusRestV1\RestClientInterface'
        );
        $mockRestClient->shouldReceive('getIdpList')->andReturn(
            array(
                'https://idp.example.edu' => array('EntityID' => 'https://idp.example.edu'),
                'https://idp2.example.edu' => array('EntityID' => 'https://idp2.example.edu'),
            )
        );
        $mockAssembler = Mockery::mock(
            'OpenConext\EngineBlock\Metadata\Entity\Assembler\JanusRestV1Assembler'
        );
        $mockAssembler->shouldReceive('assemble')
            ->with('https://idp.example.edu', Mockery::any())
            ->andReturn(new IdentityProvider('https://idp.example.edu'));
        $mockAssembler->shouldReceive('assemble')
            ->with('https://idp2.example.edu', Mockery::any())
            ->andReturn(new IdentityProvider('https://idp2.example.edu'));

        $repository = new JanusRestV1MetadataRepository($mockRestClient, $mockAssembler);
        $this->assertCount(2, $repository->findIdentityProviders());
    }
}
