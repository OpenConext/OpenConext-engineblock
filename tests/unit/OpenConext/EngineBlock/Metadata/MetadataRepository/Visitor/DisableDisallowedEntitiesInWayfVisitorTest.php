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

namespace OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor;

use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use PHPUnit\Framework\TestCase;

class DisableDisallowedEntitiesInWayfVisitorTest extends TestCase
{
    public function testVisitor()
    {
        $vistor = new DisableDisallowedEntitiesInWayfVisitor(array(
            'https://enabled.entity.com',
        ));
        $disabledIdentityProvider = new IdentityProvider('https://disabled1.entity.com');
        $this->assertTrue($disabledIdentityProvider->enabledInWayf);
        $vistor->visitIdentityProvider($disabledIdentityProvider);
        $this->assertFalse($disabledIdentityProvider->enabledInWayf);

        $enabledIdentityProvider = new IdentityProvider('https://enabled.entity.com');
        $this->assertTrue($enabledIdentityProvider->enabledInWayf);
        $vistor->visitIdentityProvider($enabledIdentityProvider);
        $this->assertTrue($enabledIdentityProvider->enabledInWayf);
    }
}
