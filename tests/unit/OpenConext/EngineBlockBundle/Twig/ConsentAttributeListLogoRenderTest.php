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

namespace Tests\OpenConext\EngineBlockBundle\Twig;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Mdui;
use OpenConext\EngineBlock\Metadata\Utils;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class ConsentAttributeListLogoRenderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testIdpLogoFallsBackToMduiLogoWhenNoDiscoveryWasSelected(): void
    {
        $idp = $this->buildIdentityProviderWithMismatchedLogos();

        $html = $this->renderAttributeList($idp, idpDiscovery: null);

        $this->assertStringContainsString('https://mdui.example.org/logo.png', $html);
        $this->assertStringNotContainsString('https://deprecated.example.org/logo.png', $html);
    }

    public function testIdpLogoFallsBackToPlaceholderWhenMduiHasNoLogo(): void
    {
        $idp = Utils::instantiate(IdentityProvider::class, [
            'entityId' => 'https://idp.example.org',
            'logo' => new Logo('https://deprecated.example.org/logo.png'),
        ]);

        $html = $this->renderAttributeList($idp, idpDiscovery: null);

        $this->assertStringContainsString('/images/placeholder.png', $html);
        $this->assertStringNotContainsString('https://deprecated.example.org/logo.png', $html);
    }

    private function buildIdentityProviderWithMismatchedLogos(): IdentityProvider
    {
        $mdui = Mdui::emptyMdui();
        $mdui->setLogo(new Logo('https://mdui.example.org/logo.png'));

        return Utils::instantiate(IdentityProvider::class, [
            'entityId' => 'https://idp.example.org',
            'mdui' => $mdui,
            // The deprecated 'logo' field intentionally holds a different URL than the mdui
            // logo, so the test can detect whether the deprecated field is still being used.
            'logo' => new Logo('https://deprecated.example.org/logo.png'),
        ]);
    }

    private function renderAttributeList(IdentityProvider $idp, ?object $idpDiscovery): string
    {
        $environment = $this->buildEnvironment();

        return $environment->render(
            '@theme/Authentication/View/Proxy/Partials/Consent/Attributes/list.html.twig',
            [
                'attributeSource' => 'idp',
                'idpName' => 'Example IdP',
                'idp' => $idp,
                'idpDiscovery' => $idpDiscovery,
                'attributes' => [],
            ],
        );
    }

    private function buildEnvironment(): Environment
    {
        $basePath = realpath(__DIR__ . '/../../../../../');

        $filesystemLoader = new FilesystemLoader();
        $filesystemLoader->addPath($basePath . '/theme/base/templates/modules', 'theme');

        $environment = new Environment($filesystemLoader);

        $translator = m::mock(TranslatorInterface::class);
        $translator->shouldReceive('trans')->andReturnUsing(static fn ($id) => (string) $id);
        $environment->addExtension(new TranslationExtension($translator));

        $environment->addFunction(new TwigFunction('attributeSourceDisplayName', static fn (string $source): string => $source));
        $environment->addFunction(new TwigFunction('attributeSourceLogoUrl', static fn (string $source): string => ''));

        return $environment;
    }
}
