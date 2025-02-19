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

namespace Tests\OpenConext\EngineBlockBundle\Twig\Extensions\Extension;

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlockBundle\Twig\Extensions\Extension\Wayf;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;
use OpenConext\EngineBlockBundle\Twig\Extensions\Extension\ConnectedIdps;


class WayfTest extends TestCase
{
    private $requestStack;
    private $translator;
    private $wayf;

    protected function setUp(): void
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->wayf = new Wayf($this->requestStack, $this->translator);
    }

    public function testGetConnectedIdpsWithEmptyPreviousSelection()
    {
        $idpList = [
            [
                'EntityID' => 'https://idp1.example.org',
                'Access' => '1',
                'Name' => 'IDP One',
                'Keywords' => ['university', 'education'],
                'Logo' => 'logo1.png',
                'isDefaultIdp' => false,
                'DiscoveryHash' => 'hash1'
            ],
            [
                'EntityID' => 'https://idp2.example.org',
                'Access' => '0',
                'Name' => 'IDP Two',
                'Keywords' => 'Undefined',
                'Logo' => 'logo2.png',
                'isDefaultIdp' => true,
                'DiscoveryHash' => 'hash2'
            ]
        ];

        $result = $this->wayf->getConnectedIdps($idpList);

        $this->assertInstanceOf(ConnectedIdps::class, $result);
        $this->assertEmpty($result->getFormattedPreviousSelectionList());

        $formattedList = $result->getFormattedIdpList();
        $this->assertCount(2, $formattedList);

        // Check first IDP
        $this->assertEquals('https://idp1.example.org', $formattedList[0]['entityId']);
        $this->assertTrue($formattedList[0]['connected']);
        $this->assertEquals('IDP One', $formattedList[0]['displayTitle']);
        $this->assertEquals('idp one', $formattedList[0]['title']);
        $this->assertEquals('university|education', $formattedList[0]['keywords']);
        $this->assertEquals('logo1.png', $formattedList[0]['logo']);
        $this->assertFalse($formattedList[0]['isDefaultIdp']);
        $this->assertEquals('hash1', $formattedList[0]['discoveryHash']);

        // Check second IDP
        $this->assertEquals('https://idp2.example.org', $formattedList[1]['entityId']);
        $this->assertFalse($formattedList[1]['connected']);
        $this->assertEquals('IDP Two', $formattedList[1]['displayTitle']);
        $this->assertEquals('idp two', $formattedList[1]['title']);
        $this->assertEquals('', $formattedList[1]['keywords']);
        $this->assertEquals('logo2.png', $formattedList[1]['logo']);
        $this->assertTrue($formattedList[1]['isDefaultIdp']);
        $this->assertEquals('hash2', $formattedList[1]['discoveryHash']);
    }

    public static function previousSelectionProvider(): array
    {
        return [
            ['https://idp1.example.org', 'idp one'],
            ['https://idp1.example.org|hash1', 'idp one discovery'],
        ];
    }

    /**
     * @dataProvider previousSelectionProvider
     */
    public function testGetConnectedIdpsWithPreviousSelection(string $storedCookieValue, string $expectedName)
    {
        // Create a mock request with cookie
        $request = $this->createMock(Request::class);
        $request->cookies = $this->getMockBuilder(stdClass::class)
            ->addMethods(['get'])
            ->getMock();
        $request->cookies->method('get')
            ->willReturn(json_encode([
                ['idp' => $storedCookieValue, 'time' => 12345]
            ]));

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')
            ->willReturn($request);

        // Create new wayf instance with the mocked request
        $wayf = new Wayf($requestStack, $this->translator);

        $idpList = [
            [
                'EntityID' => 'https://idp1.example.org',
                'Access' => '1',
                'Name' => 'IDP One',
                'Keywords' => ['university', 'education'],
                'Logo' => 'logo1.png',
                'isDefaultIdp' => false,
                'DiscoveryHash' => ''
            ],
            [
                'EntityID' => 'https://idp1.example.org',
                'Access' => '1',
                'Name' => 'IDP One Discovery',
                'Keywords' => [],
                'Logo' => 'logo2.png',
                'isDefaultIdp' => false,
                'DiscoveryHash' => 'hash1'
            ]
        ];

        $result = $wayf->getConnectedIdps($idpList);

        $this->assertInstanceOf(ConnectedIdps::class, $result);
        $previousSelection = $result->getFormattedPreviousSelectionList();
        $this->assertCount(1, $previousSelection);
        $this->assertEquals($expectedName, $previousSelection[0]['title']);
        $this->assertEquals('https://idp1.example.org', $previousSelection[0]['entityId']);
    }

    public function testGetWayfJsonConfig()
    {
        // Create mocks
        $connectedIdps = $this->createMock(ConnectedIdps::class);
        $serviceProvider = $this->createMock(ServiceProvider::class);

        // Setup mock return values
        $connectedIdps->method('getFormattedPreviousSelectionList')
            ->willReturn([['idp' => 'https://idp1.example.org', 'time' => 12345]]);

        $connectedIdps->method('getConnectedIdps')
            ->willReturn([
                [
                    'entityId' => 'https://idp1.example.org',
                    'connected' => true,
                    'displayTitle' => 'IDP One',
                    'title' => 'idp one',
                    'keywords' => 'university|education',
                    'logo' => 'logo1.png',
                    'isDefaultIdp' => false,
                    'discoveryHash' => 'hash1'
                ]
            ]);

        $connectedIdps->method('getFormattedIdpList')
            ->willReturn([
                [
                    'entityId' => 'https://idp1.example.org',
                    'connected' => true,
                    'displayTitle' => 'IDP One',
                    'title' => 'idp one',
                    'keywords' => 'university|education',
                    'logo' => 'logo1.png',
                    'isDefaultIdp' => false,
                    'discoveryHash' => 'hash1'
                ],
                [
                    'entityId' => 'https://idp2.example.org',
                    'connected' => false,
                    'displayTitle' => 'IDP Two',
                    'title' => 'idp two',
                    'keywords' => '',
                    'logo' => 'logo2.png',
                    'isDefaultIdp' => true,
                    'discoveryHash' => 'hash2'
                ]
            ]);

        $serviceProvider->entityId = 'https://sp.example.org';
        $serviceProvider->method('getDisplayName')
            ->willReturn('Test SP');

        // Setup translator
        $this->translator->method('trans')
            ->willReturnMap([
                ['more_idp_results', [], null, null, 'More results'],
                ['request_access', [], null, null, 'Request Access']
            ]);

        // Test with showRequestAccess = true
        $jsonConfig = $this->wayf->getWayfJsonConfig(
            $connectedIdps,
            $serviceProvider,
            'en',
            true,
            true,
            5
        );

        $config = json_decode($jsonConfig, true);

        $this->assertEquals(Wayf::PREVIOUS_SELECTION_COOKIE_NAME, $config['previousSelectionCookieName']);
        $this->assertEquals([['idp' => 'https://idp1.example.org', 'time' => 12345]], $config['previousSelectionList']);
        $this->assertCount(1, $config['connectedIdps']);
        $this->assertCount(1, $config['unconnectedIdps']);
        $this->assertEquals(5, $config['cutoffPointForShowingUnfilteredIdps']);
        $this->assertEquals(Wayf::REMEMBER_CHOICE_COOKIE_NAME, $config['rememberChoiceCookieName']);
        $this->assertTrue($config['rememberChoiceFeature']);
        $this->assertEquals('More results', $config['messages']['moreIdpResults']);
        $this->assertEquals('Request Access', $config['messages']['requestAccess']);
        $this->assertStringContainsString('/authentication/idp/requestAccess', $config['requestAccessUrl']);

        // Test with showRequestAccess = false
        $jsonConfig = $this->wayf->getWayfJsonConfig(
            $connectedIdps,
            $serviceProvider,
            'en',
            false,
            true,
            5
        );

        $config = json_decode($jsonConfig, true);
        $this->assertEmpty($config['unconnectedIdps']);
    }
}
