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

declare(strict_types=1);

namespace Tests\OpenConext\EngineBlockBundle\Service;

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Service\Wayf\IdpSplitter;
use OpenConext\EngineBlock\Service\Wayf\WayfIdp;
use OpenConext\EngineBlockBundle\Service\WayfRenderer;
use OpenConext\EngineBlockBundle\Service\WayfViewModelFactory;
use OpenConext\EngineBlockBundle\Twig\Extensions\Extension\ConnectedIdps;
use OpenConext\EngineBlockBundle\ViewModel\WayfViewModel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class WayfRendererTest extends TestCase
{
    private WayfViewModelFactory $factory;
    private Environment $twig;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(WayfViewModelFactory::class);
        $this->twig = $this->createMock(Environment::class);
    }

    private function renderer(): WayfRenderer
    {
        return new WayfRenderer(
            factory: $this->factory,
            splitter: new IdpSplitter(),
            twig: $this->twig,
        );
    }

    private function buildViewModel(bool $showIdPBanner = false, bool $showPreferredIdps = false): WayfViewModel
    {
        $emptyIdps = new ConnectedIdps([], []);
        $sp = $this->createStub(ServiceProvider::class);

        return new WayfViewModel(
            action: '/sso',
            greenHeader: 'SP',
            helpLink: '/help',
            backLink: false,
            cutoffPointForShowingUnfilteredIdps: 100,
            showIdPBanner: $showIdPBanner,
            rememberChoiceFeature: false,
            showRequestAccess: false,
            showRequestAccessContainer: true,
            requestId: 'req-1',
            serviceProvider: $sp,
            connectedIdps: $emptyIdps,
            regularConnectedIdps: $emptyIdps,
            preferredConnectedIdps: $emptyIdps,
            showPreferredIdps: $showPreferredIdps,
            idpList: [],
            regularIdpList: [],
            preferredIdpList: [],
        );
    }

    #[DataProvider('bannerConditionProvider')]
    public function testBannerConditionPassedToFactory(
        bool $shouldDisplayBanner,
        bool $defaultIdpInList,
        array $preferredIdpEntityIds,
        string $defaultIdpEntityId,
        bool $expectedShowBanner,
    ): void {
        $idpList = $defaultIdpInList
            ? [new WayfIdp(name: null, logo: '', keywords: [], accessible: true, id: md5($defaultIdpEntityId), entityId: $defaultIdpEntityId, isDefaultIdp: true, discoveryHash: null)]
            : [new WayfIdp(name: null, logo: '', keywords: [], accessible: true, id: md5('other'), entityId: 'https://other.example.org', isDefaultIdp: false, discoveryHash: null)];

        $capturedShowIdPBanner = null;

        $this->factory->expects($this->once())
            ->method('create')
            ->willReturnCallback(function () use (&$capturedShowIdPBanner): WayfViewModel {
                $namedArgs = func_get_args();
                $capturedShowIdPBanner = $namedArgs[9];
                return $this->buildViewModel($namedArgs[9]);
            });

        $this->twig->method('render')->willReturn('<html>');

        $sp = $this->createStub(ServiceProvider::class);
        $sp->method('getDisplayName')->willReturn('Test SP');

        $this->renderer()->render(
            idpList: $idpList,
            preferredIdpEntityIds: $preferredIdpEntityIds,
            action: '/sso',
            currentLocale: 'en',
            defaultIdpEntityId: $defaultIdpEntityId,
            shouldDisplayBanner: $shouldDisplayBanner,
            backLink: false,
            cutoffPoint: 100,
            rememberChoice: false,
            showRequestAccess: false,
            requestId: 'req-1',
            serviceProvider: $sp,
        );

        $this->assertSame($expectedShowBanner, $capturedShowIdPBanner);
    }

    public static function bannerConditionProvider(): array
    {
        $defaultId = 'https://default.example.org';
        $otherId = 'https://other.example.org';

        return [
            'banner off by config'                                          => [false, true,  [],          $defaultId, false],
            'banner on, default not in list'                                => [true,  false, [],          $defaultId, false],
            'banner on, no preferred IdPs'                                  => [true,  true,  [],          $defaultId, true],
            'banner on, default is preferred (suppressed)'                  => [true,  true,  [$defaultId], $defaultId, false],
            'banner on, preferred shown but default is not one of them'     => [true,  true,  [$otherId],  $defaultId, true],
        ];
    }

    public function testSplitsIdpListBeforePassingToFactory(): void
    {
        $preferredId = 'https://preferred.example.org';
        $regularId = 'https://regular.example.org';

        $idpList = [
            new WayfIdp(name: null, logo: '', keywords: [], accessible: true, id: md5($preferredId), entityId: $preferredId, isDefaultIdp: false, discoveryHash: null),
            new WayfIdp(name: null, logo: '', keywords: [], accessible: true, id: md5($regularId), entityId: $regularId, isDefaultIdp: false, discoveryHash: null),
        ];

        $capturedRegular = null;
        $capturedPreferred = null;

        $this->factory->expects($this->once())
            ->method('create')
            ->willReturnCallback(function (
                array $idpList,
                array $regularIdpList,
                array $preferredIdpList,
            ) use (
                &$capturedRegular,
                &$capturedPreferred
            ): WayfViewModel {
                $capturedRegular = $regularIdpList;
                $capturedPreferred = $preferredIdpList;
                return $this->buildViewModel();
            });

        $this->twig->method('render')->willReturn('<html>');

        $sp = $this->createStub(ServiceProvider::class);
        $sp->method('getDisplayName')->willReturn('Test SP');

        $this->renderer()->render(
            idpList: $idpList,
            preferredIdpEntityIds: [$preferredId],
            action: '/sso',
            currentLocale: 'en',
            defaultIdpEntityId: '',
            shouldDisplayBanner: false,
            backLink: false,
            cutoffPoint: 100,
            rememberChoice: false,
            showRequestAccess: false,
            requestId: 'req-1',
            serviceProvider: $sp,
        );

        $this->assertCount(1, $capturedPreferred);
        $this->assertSame($preferredId, $capturedPreferred[0]->entityId);
        $this->assertCount(1, $capturedRegular);
        $this->assertSame($regularId, $capturedRegular[0]->entityId);
    }

    public function testReturnsRenderedHtml(): void
    {
        $expectedHtml = '<html><body>WAYF</body></html>';

        $this->factory->method('create')->willReturn($this->buildViewModel());
        $this->twig->method('render')->willReturn($expectedHtml);

        $sp = $this->createStub(ServiceProvider::class);
        $sp->method('getDisplayName')->willReturn('SP');

        $result = $this->renderer()->render(
            idpList: [],
            preferredIdpEntityIds: [],
            action: '/sso',
            currentLocale: 'en',
            defaultIdpEntityId: '',
            shouldDisplayBanner: false,
            backLink: false,
            cutoffPoint: 100,
            rememberChoice: false,
            showRequestAccess: false,
            requestId: 'req-1',
            serviceProvider: $sp,
        );

        $this->assertSame($expectedHtml, $result);
    }
}
