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
use OpenConext\EngineBlockBundle\Service\RememberChoiceDurationFormatter;
use OpenConext\EngineBlockBundle\Service\WayfViewModelFactory;
use OpenConext\EngineBlockBundle\Twig\Extensions\Extension\ConnectedIdps;
use OpenConext\EngineBlockBundle\Twig\Extensions\Extension\Wayf;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class WayfViewModelFactoryTest extends TestCase
{
    public function testRememberChoiceDurationIsTakenFromTheFormatter(): void
    {
        $wayfExtension = $this->createMock(Wayf::class);
        $wayfExtension->method('getConnectedIdps')->willReturn(new ConnectedIdps([], []));

        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturn('90 days');
        $durationFormatter = new RememberChoiceDurationFormatter($translator, 7776000);

        $factory = new WayfViewModelFactory($wayfExtension, $durationFormatter);

        $viewModel = $factory->create(
            idpList: [],
            regularIdpList: [],
            preferredIdpList: [],
            showPreferredIdps: false,
            action: '/sso',
            greenHeader: 'SP',
            helpLink: '/help',
            backLink: false,
            cutoffPointForShowingUnfilteredIdps: 100,
            showIdPBanner: false,
            rememberChoiceFeature: true,
            showRequestAccess: false,
            requestId: 'req-1',
            serviceProvider: $this->createStub(ServiceProvider::class),
            rememberChoicePerIdp: true,
        );

        $this->assertSame('90 days', $viewModel->rememberChoiceDuration);
    }

    public function testRememberChoiceDurationIsEmptyWhenNotPerIdp(): void
    {
        $wayfExtension = $this->createMock(Wayf::class);
        $wayfExtension->method('getConnectedIdps')->willReturn(new ConnectedIdps([], []));

        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturn('90 days');
        $durationFormatter = new RememberChoiceDurationFormatter($translator, 7776000);

        $factory = new WayfViewModelFactory($wayfExtension, $durationFormatter);

        $viewModel = $factory->create(
            idpList: [],
            regularIdpList: [],
            preferredIdpList: [],
            showPreferredIdps: false,
            action: '/sso',
            greenHeader: 'SP',
            helpLink: '/help',
            backLink: false,
            cutoffPointForShowingUnfilteredIdps: 100,
            showIdPBanner: false,
            rememberChoiceFeature: true,
            showRequestAccess: false,
            requestId: 'req-1',
            serviceProvider: $this->createStub(ServiceProvider::class),
            rememberChoicePerIdp: false,
        );

        $this->assertSame('', $viewModel->rememberChoiceDuration);
    }
}
