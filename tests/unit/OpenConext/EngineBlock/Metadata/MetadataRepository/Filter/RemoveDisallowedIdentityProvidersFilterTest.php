<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlock\Metadata\MetadataRepository\Filter;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RemoveDisallowedIdentityProvidersFilterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

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
