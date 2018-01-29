<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository\Filter;

use Mockery;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class RemoveDisallowedIdentityProvidersFilter
 *
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository\Filter
 */
class RemoveEntityByEntityIdFilterTest extends PHPUnit_Framework_TestCase
{

    public function testRemoveEntityId()
    {
        $filter = new RemoveEntityByEntityIdFilter('https://bad.entityid.example.edu');
        $this->assertNull($filter->filterRole(new IdentityProvider('https://bad.entityid.example.edu')));
        $this->assertNotNull($filter->filterRole(new IdentityProvider('https://good.entityid.example.edu')));
    }

    public function testLogging()
    {
        $mockLogger = Mockery::mock(LoggerInterface::class);
        $mockLogger
            ->shouldReceive('debug')
            ->with('Invalid EntityId found (OpenConext\EngineBlock\Metadata\MetadataRepository\Filter\RemoveEntityByEntityIdFilter -> https://bad.entityid.example.edu)');
        $filter = new RemoveEntityByEntityIdFilter('https://bad.entityid.example.edu');
        $this->assertNull($filter->filterRole(new IdentityProvider('https://bad.entityid.example.edu'), $mockLogger));
    }
}
