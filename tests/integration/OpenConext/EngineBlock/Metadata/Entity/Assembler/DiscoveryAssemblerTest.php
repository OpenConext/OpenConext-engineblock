<?php

/**
 * Copyright 2025 SURFnet B.V.
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

namespace OpenConext\EngineBlock\Tests;

use OpenConext\EngineBlock\Metadata\Discovery;
use OpenConext\EngineBlock\Metadata\Entity\Assembler\DiscoveryAssembler;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlockBundle\Localization\LanguageSupportProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class DiscoveryAssemblerTest extends TestCase
{
    private $discoveryAssembler;

    protected function setUp(): void
    {
        $languageSupportProvider = $this->createMock(LanguageSupportProvider::class);
        $languageSupportProvider
            ->method('getSupportedLanguages')
            ->willReturn(['en', 'fr', 'de']);

        $this->discoveryAssembler = new DiscoveryAssembler($languageSupportProvider);
    }

    public function testAssembleDiscoveriesReturnsEmptyArrayWhenNoDiscoveries(): void
    {
        $connection = new stdClass();
        $connection->metadata = new stdClass();

        $result = $this->discoveryAssembler->assembleDiscoveries($connection);

        $this->assertEquals([], $result);
    }

    public function testAssembleDiscoveriesProcessesValidDiscovery(): void
    {
        $connection = new stdClass();
        $connection->metadata = new stdClass();
        $connection->metadata->discoveries = [
            $this->createDiscoveryObject([
                'name_en' => 'Test Discovery',
                'name_fr' => 'Découverte Test',
                'keywords_en' => 'test, discovery',
                'keywords_fr' => 'test, découverte',
                'logo_url' => 'http://example.com/logo.png',
                'logo_width' => 100,
                'logo_height' => 100
            ])
        ];

        $result = $this->discoveryAssembler->assembleDiscoveries($connection);

        $this->assertArrayHasKey('discoveries', $result);
        $this->assertCount(1, $result['discoveries']);
        $this->assertInstanceOf(Discovery::class, $result['discoveries'][0]);

        /** @var Discovery $discovery */
        $discovery = $result['discoveries'][0];
        $this->assertEquals('Test Discovery', $discovery->getName('en'));
        $this->assertEquals('Découverte Test', $discovery->getName('fr'));
        $this->assertEquals('test, discovery', $discovery->getKeywords('en'));
        $this->assertEquals('test, découverte', $discovery->getKeywords('fr'));

        $this->assertInstanceOf(Logo::class, $discovery->getLogo());
        $this->assertEquals('http://example.com/logo.png', $discovery->getLogo()->url);
        $this->assertEquals(100, $discovery->getLogo()->width);
        $this->assertEquals(100, $discovery->getLogo()->height);
    }

    public function testAssembleDiscoveriesSkipsDiscoveryWithoutEnglishName(): void
    {
        $connection = new stdClass();
        $connection->metadata = new stdClass();
        $connection->metadata->discoveries = [
            $this->createDiscoveryObject([
                'name_fr' => 'Découverte Test',
                'keywords_fr' => 'test, découverte'
            ])
        ];

        $result = $this->discoveryAssembler->assembleDiscoveries($connection);

        $this->assertEquals([], $result);
    }

    public function testAssembleDiscoveriesWithTrimmedValues(): void
    {
        $connection = new stdClass();
        $connection->metadata = new stdClass();
        $connection->metadata->discoveries = [
            $this->createDiscoveryObject([
                'name_en' => '  Test Discovery  ',
                'name_fr' => '  Découverte Test  ',
                'keywords_en' => '  test, discovery  ',
                'keywords_fr' => '  test, découverte  '
            ])
        ];

        $result = $this->discoveryAssembler->assembleDiscoveries($connection);

        $this->assertArrayHasKey('discoveries', $result);
        /** @var Discovery $discovery */
        $discovery = $result['discoveries'][0];
        $this->assertEquals('Test Discovery', $discovery->getName('en'));
        $this->assertEquals('Découverte Test', $discovery->getName('fr'));
        $this->assertEquals('test, discovery', $discovery->getKeywords('en'));
        $this->assertEquals('test, découverte', $discovery->getKeywords('fr'));
    }

    public function testAssembleDiscoveriesWithEmptyLogoUrl(): void
    {
        $connection = new stdClass();
        $connection->metadata = new stdClass();
        $connection->metadata->discoveries = [
            $this->createDiscoveryObject([
                'name_en' => 'Test Discovery',
                'logo_url' => '',
                'logo_width' => 100,
                'logo_height' => 100
            ])
        ];

        $result = $this->discoveryAssembler->assembleDiscoveries($connection);

        $this->assertArrayHasKey('discoveries', $result);
        $this->assertFalse($result['discoveries'][0]->hasLogo());
    }

    public function testAssembleDiscoveriesWithLogoWithoutDimensions(): void
    {
        $connection = new stdClass();
        $connection->metadata = new stdClass();
        $connection->metadata->discoveries = [
            $this->createDiscoveryObject([
                'name_en' => 'Test Discovery',
                'logo_url' => 'http://example.com/logo.png'
            ])
        ];

        $result = $this->discoveryAssembler->assembleDiscoveries($connection);

        $this->assertArrayHasKey('discoveries', $result);
        /** @var Discovery $discovery */
        $discovery = $result['discoveries'][0];
        $this->assertInstanceOf(Logo::class, $discovery->getLogo());
        $this->assertEquals('http://example.com/logo.png', $discovery->getLogo()->url);
        $this->assertNull($discovery->getLogo()->width);
        $this->assertNull($discovery->getLogo()->height);
    }

    private function createDiscoveryObject(array $properties): stdClass
    {
        $discovery = new stdClass();
        foreach ($properties as $key => $value) {
            $discovery->$key = $value;
        }
        return $discovery;
    }
}
