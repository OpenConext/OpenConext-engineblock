<?php declare(strict_types=1);

/**
 * Copyright 2026 SURFnet B.V.
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

namespace OpenConext\EngineBlock\Saml2;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DefaultIdGeneratorTest extends TestCase
{
    private DefaultIdGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new DefaultIdGenerator();
    }

    #[Test]
    public function generateReturnsStringWithGivenPrefix(): void
    {
        $id = $this->generator->generate('EB', IdGenerator::ID_USAGE_OTHER);

        self::assertStringStartsWith('EB', $id);
    }

    #[Test]
    public function generateReturnsUniqueIds(): void
    {
        $id1 = $this->generator->generate();
        $id2 = $this->generator->generate();

        self::assertNotSame($id1, $id2);
    }

    #[Test]
    public function generateDefaultsToEbPrefix(): void
    {
        $id = $this->generator->generate();

        self::assertStringStartsWith('EB', $id);
    }

    #[Test]
    public function generateReturnsFortyHexCharsAfterPrefix(): void
    {
        $id = $this->generator->generate('EB', IdGenerator::ID_USAGE_OTHER);

        self::assertMatchesRegularExpression('/^EB[0-9a-f]{40}$/', $id);
    }
}
