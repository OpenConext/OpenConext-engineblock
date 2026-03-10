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

namespace OpenConext\EngineBlock\Authentication\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\TestDataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SchacHomeOrganizationTest extends TestCase
{
    /**
     *
     * @param mixed $notStringOrEmptyString
     */
    #[DataProviderExternal(TestDataProvider::class, 'notStringOrEmptyString')]
    #[Group('EngineBlock')]
    #[Group('Authentication')]
    #[Test]
    public function schac_home_organization_must_be_a_non_empty_string($notStringOrEmptyString)
    {
        $this->expectException(InvalidArgumentException::class);

        new SchacHomeOrganization($notStringOrEmptyString);
    }

    #[Group('EngineBlock')]
    #[Group('Authentication')]
    #[Test]
    public function schac_home_organization_can_be_retrieved()
    {
        $schacHomeOrganizationValue = 'OpenConext.org';

        $schacHomeOrganization = new SchacHomeOrganization($schacHomeOrganizationValue);

        $this->assertSame($schacHomeOrganizationValue, $schacHomeOrganization->getSchacHomeOrganization());
    }

    #[Group('EngineBlock')]
    #[Group('Authentication')]
    #[Test]
    public function schac_home_organization_equality_is_determined_based_on_value()
    {
        $base      = new SchacHomeOrganization('OpenConext.org');
        $same      = new SchacHomeOrganization('OpenConext.org');
        $different = new SchacHomeOrganization('BabelFish Inc.');

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($different));
    }

    #[Group('EngineBlock')]
    #[Group('Authentication')]
    #[Test]
    public function a_schac_home_organization_can_be_cast_to_string()
    {
        $schacHomeOrganization = new SchacHomeOrganization('OpenConext.org');

        $this->assertIsString((string) $schacHomeOrganization);
    }
}
