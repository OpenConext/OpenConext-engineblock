<?php

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

namespace Tests\OpenConext\EngineBlock\Service\Wayf;

use OpenConext\EngineBlock\Service\Wayf\IdpSplitter;
use OpenConext\EngineBlock\Service\Wayf\WayfIdp;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class IdpSplitterTest extends TestCase
{
    private IdpSplitter $splitter;

    protected function setUp(): void
    {
        $this->splitter = new IdpSplitter();
    }

    private function split(array $idpList, array $preferredEntityIds): array
    {
        return $this->splitter->split($idpList, $preferredEntityIds);
    }

    private function idp(string $entityId, string $access = '1', string $discoveryHash = ''): WayfIdp
    {
        return new WayfIdp(
            name: $entityId,
            logo: '',
            keywords: [],
            accessible: $access === '1',
            id: md5($entityId),
            entityId: $entityId,
            isDefaultIdp: false,
            discoveryHash: $discoveryHash !== '' ? $discoveryHash : null,
        );
    }

    public function testEmptyPreferredEntityIdsReturnsFullListAsRegular(): void
    {
        $idpList = [$this->idp('https://idp1.example.org'), $this->idp('https://idp2.example.org')];

        $result = $this->split($idpList, []);

        $this->assertSame([], $result['preferred']);
        $this->assertSame($idpList, $result['regular']);
    }

    public function testPreferredIdpIsMovedOutOfRegularList(): void
    {
        $idp1 = $this->idp('https://idp1.example.org');
        $idp2 = $this->idp('https://idp2.example.org');

        $result = $this->split([$idp1, $idp2], ['https://idp1.example.org']);

        $this->assertCount(1, $result['preferred']);
        $this->assertSame('https://idp1.example.org', $result['preferred'][0]->entityId);
        $this->assertCount(1, $result['regular']);
        $this->assertSame('https://idp2.example.org', $result['regular'][0]->entityId);
    }

    public function testConfiguredOrderIsPreservedInPreferredList(): void
    {
        $idp1 = $this->idp('https://idp1.example.org');
        $idp2 = $this->idp('https://idp2.example.org');
        $idp3 = $this->idp('https://idp3.example.org');

        $result = $this->split(
            [$idp1, $idp2, $idp3],
            ['https://idp3.example.org', 'https://idp1.example.org']
        );

        $this->assertCount(2, $result['preferred']);
        $this->assertSame('https://idp3.example.org', $result['preferred'][0]->entityId);
        $this->assertSame('https://idp1.example.org', $result['preferred'][1]->entityId);
        $this->assertCount(1, $result['regular']);
        $this->assertSame('https://idp2.example.org', $result['regular'][0]->entityId);
    }

    public function testDisconnectedPreferredIdpIsExcludedFromBothLists(): void
    {
        $idpConnected    = $this->idp('https://idp1.example.org', '1');
        $idpDisconnected = $this->idp('https://idp2.example.org', '0');

        $result = $this->split(
            [$idpConnected, $idpDisconnected],
            ['https://idp2.example.org']
        );

        $this->assertSame([], $result['preferred']);
        // idp1 is not preferred so it stays in regular; idp2 is preferred but disconnected > excluded from both
        $this->assertCount(1, $result['regular']);
        $this->assertSame('https://idp1.example.org', $result['regular'][0]->entityId);
    }

    public function testMultipleDiscoveryEntriesForSameEntityIdAreGroupedInOrder(): void
    {
        $idpMain      = $this->idp('https://idp1.example.org', '1', '');
        $idpDiscovery = $this->idp('https://idp1.example.org', '1', 'discovery-hash');
        $idpOther     = $this->idp('https://idp2.example.org', '1', '');

        $result = $this->split(
            [$idpMain, $idpDiscovery, $idpOther],
            ['https://idp1.example.org']
        );

        $this->assertCount(2, $result['preferred']);
        $this->assertSame('https://idp1.example.org', $result['preferred'][0]->entityId);
        $this->assertSame('https://idp1.example.org', $result['preferred'][1]->entityId);
        $this->assertCount(1, $result['regular']);
        $this->assertSame('https://idp2.example.org', $result['regular'][0]->entityId);
    }

    public function testPreferredEntityIdNotInIdpListIsIgnored(): void
    {
        $idp1 = $this->idp('https://idp1.example.org');

        $result = $this->split([$idp1], ['https://nonexistent.example.org']);

        $this->assertSame([], $result['preferred']);
        $this->assertCount(1, $result['regular']);
    }

    public static function fiveScenarioProvider(): array
    {
        return [
            'scenario 1: no preferred, default connected'    => [[], true, 'https://default.example.org', false, true],
            'scenario 2: no preferred, default not connected' => [[], false, 'https://default.example.org', false, false],
            'scenario 3: preferred includes default'          => [['https://default.example.org'], true, 'https://default.example.org', true, false],
            'scenario 4: preferred does not include default'  => [['https://idp1.example.org'], true, 'https://default.example.org', true, true],
            'scenario 5: preferred, default not connected'    => [['https://idp1.example.org'], false, 'https://default.example.org', true, false],
        ];
    }

    #[DataProvider('fiveScenarioProvider')]
    public function testFiveScenarioBannerAndPreferredVisibility(
        array  $preferredEntityIds,
        bool   $defaultIdpConnected,
        string $defaultIdpEntityId,
        bool   $expectShowPreferred,
        bool   $expectShowBanner
    ): void {
        $idpList = [
            $this->idp('https://idp1.example.org', '1'),
            $this->idp('https://default.example.org', $defaultIdpConnected ? '1' : '0'),
        ];

        $split = $this->split($idpList, $preferredEntityIds);

        $showPreferred      = !empty($split['preferred']);
        $isDefaultPreferred = in_array($defaultIdpEntityId, $preferredEntityIds, true);
        $showBanner         = $defaultIdpConnected && (!$showPreferred || !$isDefaultPreferred);

        $this->assertSame($expectShowPreferred, $showPreferred, 'showPreferredIdps mismatch');
        $this->assertSame($expectShowBanner, $showBanner, 'showIdPBanner mismatch');
    }
}
