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

namespace OpenConext\EngineBlockBundle\Service;

use OpenConext\EngineBlock\Metadata\Discovery;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Logo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DiscoverySelectionServiceTest extends TestCase
{
    /**
     * @var DiscoverySelectionService
     */
    private $service;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var IdentityProvider
     */
    private $identityProvider;

    /**
     * @var Discovery
     */
    private $discovery;

    protected function setUp(): void
    {
        $this->service = new DiscoverySelectionService();
        $this->session = $this->createMock(SessionInterface::class);
        $this->identityProvider = $this->createMock(IdentityProvider::class);
        $this->discovery = Discovery::create(['en' => 'IdP'], [], new Logo('https://example.org/image.png'));
    }

    public function testDiscoveryMatchesHashReturnsTrue()
    {
        $hash = hash('sha256', json_encode($this->discovery));

        $this->assertTrue(
            $this->service->discoveryMatchesHash($this->discovery, $hash)
        );
    }

    public function testDiscoveryMatchesHashReturnsFalse()
    {
        $hash = 'invalid_hash';

        $this->assertFalse(
            $this->service->discoveryMatchesHash($this->discovery, $hash)
        );
    }

    public function testGetDiscoveryFromRequestReturnsMatchingDiscovery()
    {
        $hash = hash('sha256', json_encode($this->discovery));

        $this->session->expects($this->once())
            ->method('get')
            ->with(
                'discovery',
                false
            )
            ->willReturn($hash);

        $this->identityProvider->expects($this->once())
            ->method('getDiscoveries')
            ->willReturn([$this->discovery]);

        $result = $this->service->getDiscoveryFromRequest($this->session, $this->identityProvider);

        $this->assertSame($this->discovery, $result);
    }

    public function testGetDiscoveryFromRequestReturnsNullWhenNoMatch()
    {
        $this->session->expects($this->once())
            ->method('get')
            ->with(
                'discovery',
                false
            )
            ->willReturn('non_matching_hash');

        $this->identityProvider->expects($this->once())
            ->method('getDiscoveries')
            ->willReturn([$this->discovery]);

        $result = $this->service->getDiscoveryFromRequest($this->session, $this->identityProvider);

        $this->assertNull($result);
    }

    public function testGetDiscoveryFromRequestWithEmptySession()
    {
        $this->session->expects($this->once())
            ->method('get')
            ->with(
                'discovery',
                false
            )
            ->willReturn(false);

        $this->identityProvider->expects($this->once())
            ->method('getDiscoveries')
            ->willReturn([$this->discovery]);

        $result = $this->service->getDiscoveryFromRequest($this->session, $this->identityProvider);

        $this->assertNull($result);
    }

    public function testRegisterDiscoveryHashStoresHashInSession()
    {
        $hash = 'test_hash';

        $this->session->expects($this->once())
            ->method('set')
            ->with('discovery', $hash);

        $this->service->registerDiscoveryHash($this->session, $hash);
    }

    public function testClearDiscoveryHashRemovesHashFromSession()
    {
        $this->session->expects($this->once())
            ->method('remove')
            ->with('discovery');

        $this->service->clearDiscoveryHash($this->session);
    }

    public function testGetDiscoveryFromRequestWithMultipleDiscoveries()
    {
        $discovery2 = $this->createMock(Discovery::class);
        $hash = hash('sha256', json_encode($discovery2));

        $this->session->expects($this->once())
            ->method('get')
            ->with(
                'discovery',
                false
            )
            ->willReturn($hash);

        $this->identityProvider->expects($this->once())
            ->method('getDiscoveries')
            ->willReturn([$this->discovery, $discovery2]);

        $result = $this->service->getDiscoveryFromRequest($this->session, $this->identityProvider);

        $this->assertEquals($discovery2, $result);
    }

    public function testHashGeneratesCorrectHash()
    {
        $discoveryJson = json_encode($this->discovery);
        $expectedHash = hash('sha256', $discoveryJson);

        $result = $this->service->hash($this->discovery);

        $this->assertEquals($expectedHash, $result);
    }

}
