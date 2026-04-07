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

use OpenConext\EngineBlock\Authentication\Value\ConsentVersion;
use PHPUnit\Framework\TestCase;
use ValueError;

class ConsentVersionTest extends TestCase
{
    public function testStableIsGiven(): void
    {
        $version = ConsentVersion::Stable;

        $this->assertTrue($version->given());
        $this->assertTrue($version->isStable());
        $this->assertFalse($version->isUnstable());
        $this->assertSame('stable', $version->value);
    }

    public function testUnstableIsGiven(): void
    {
        $version = ConsentVersion::Unstable;

        $this->assertTrue($version->given());
        $this->assertFalse($version->isStable());
        $this->assertTrue($version->isUnstable());
        $this->assertSame('unstable', $version->value);
    }

    public function testNotGivenIsNotGiven(): void
    {
        $version = ConsentVersion::NotGiven;

        $this->assertFalse($version->given());
        $this->assertFalse($version->isStable());
        $this->assertFalse($version->isUnstable());
        $this->assertSame('not-given', $version->value);
    }

    public function testInvalidVersionThrows(): void
    {
        $this->expectException(ValueError::class);
        ConsentVersion::from('invalid');
    }
}
