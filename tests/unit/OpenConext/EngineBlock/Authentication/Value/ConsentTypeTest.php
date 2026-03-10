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

namespace OpenConext\EngineBlock\Authentication\Tests\Value;

use OpenConext\EngineBlock\Authentication\Value\ConsentType;
use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\TestDataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ConsentTypeTest extends TestCase
{
    /**
     *
     *
     * @param mixed $invalid
     */
    #[DataProviderExternal(TestDataProvider::class, 'notStringOrEmptyString')]
    #[Group('EngineBlock')]
    #[Group('Authentication')]
    #[Group('Value')]
    #[Test]
    public function cannot_be_other_than_implicit_or_explicit($invalid)
    {
        $this->expectException(InvalidArgumentException::class);

        new ConsentType($invalid);
    }

    #[Group('EngineBlock')]
    #[Group('Authentication')]
    #[Group('Value')]
    #[Test]
    public function different_consent_types_are_not_equal()
    {
        $explicit = ConsentType::explicit();
        $implicit = ConsentType::implicit();

        $this->assertFalse($explicit->equals($implicit));
        $this->assertFalse($implicit->equals($explicit));
    }

    #[Group('EngineBlock')]
    #[Group('Authentication')]
    #[Group('Value')]
    #[Test]
    public function same_type_of_consent_types_are_equal()
    {
        $explicit = ConsentType::explicit();
        $implicit = ConsentType::implicit();

        $this->assertTrue($explicit->equals(ConsentType::explicit()));
        $this->assertTrue($implicit->equals(ConsentType::implicit()));
    }
}
