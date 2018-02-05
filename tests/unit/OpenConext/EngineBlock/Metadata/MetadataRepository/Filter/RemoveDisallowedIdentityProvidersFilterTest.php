<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository\Filter;

use Mockery;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class RemoveDisallowedIdentityProvidersFilter
 *
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository\Filter
 */
class RemoveDisallowedIdentityProvidersFilterTest extends PHPUnit_Framework_TestCase
{
    public function testRemove()
    {
        $filter = new RemoveDisallowedIdentityProvidersFilter(
            'https://entityid',
            array('https://allowed.entity.com')
        );
        $mockDisallowedIdpRole = new IdentityProvider('https://disallowed.entity.com');
        $this->assertNull($filter->filterRole($mockDisallowedIdpRole));
        $mockAllowedSpRole = new ServiceProvider('https://disallowed.entity.com');
        $this->assertNotNull($filter->filterRole($mockAllowedSpRole));
        $mockAllowedIdpRole = new IdentityProvider('https://allowed.entity.com');
        $this->assertNotNull($filter->filterRole($mockAllowedIdpRole));
    }

    public function testLogging()
    {
        $mockLogger = Mockery::mock(LoggerInterface::class);
        $mockLogger
            ->shouldReceive('debug')
            ->with('Identity Provider is not allowed (OpenConext\EngineBlock\Metadata\MetadataRepository\Filter\RemoveDisallowedIdentityProvidersFilter -> https://entityid)');
        $filter = new RemoveDisallowedIdentityProvidersFilter(
            'https://entityid',
            array('https://allowed.entity.com')
        );
        $mockDisallowedIdpRole = new IdentityProvider('https://disallowed.entity.com');
        $this->assertNull($filter->filterRole($mockDisallowedIdpRole, $mockLogger));
    }
}
