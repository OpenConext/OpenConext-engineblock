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
use PHPUnit\Framework\TestCase;

class ConsentTypeTest extends TestCase
{
    /**
     * @param mixed $invalid
     */
    #[\PHPUnit\Framework\Attributes\DataProviderExternal(\OpenConext\TestDataProvider::class, 'notStringOrEmptyString')]
    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Authentication')]
    #[\PHPUnit\Framework\Attributes\Group('Value')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function cannot_be_other_than_implicit_or_explicit($invalid)
    {
        $this->expectException(\Throwable::class);

        ConsentType::from($invalid);
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Authentication')]
    #[\PHPUnit\Framework\Attributes\Group('Value')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invalid_string_is_rejected()
    {
        $this->expectException(\ValueError::class);

        ConsentType::from('not-a-valid-consent-type');
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Authentication')]
    #[\PHPUnit\Framework\Attributes\Group('Value')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function different_consent_types_are_not_equal()
    {
        $this->assertNotSame(ConsentType::Explicit, ConsentType::Implicit);
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Authentication')]
    #[\PHPUnit\Framework\Attributes\Group('Value')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function same_type_of_consent_types_are_equal()
    {
        $this->assertSame(ConsentType::Explicit, ConsentType::Explicit);
        $this->assertSame(ConsentType::Implicit, ConsentType::Implicit);
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Authentication')]
    #[\PHPUnit\Framework\Attributes\Group('Value')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function can_be_created_from_string()
    {
        $this->assertSame(ConsentType::Explicit, ConsentType::from('explicit'));
        $this->assertSame(ConsentType::Implicit, ConsentType::from('implicit'));
    }
}
