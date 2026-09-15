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
use OpenConext\EngineBlockBundle\Twig\Extensions\Extension\Spaceless;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Extension\AttributeExtension;
use Twig\Loader\FilesystemLoader;

/**
 * Renders the real `rememberChoice.html.twig` (and the `rememberChoiceTooltip.html.twig` it includes
 * for per-SP mode) through an actual Twig Environment backed by the FilesystemLoader, using a real
 * TranslationExtension. This is the template that was previously unreachable for `rememberChoicePerIdp`
 * because `remainingIdps.html.twig` never forwarded that flag (or the duration) into it.
 */
class WayfRememberChoiceRenderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testGlobalVariantRendersPlainTextWithoutATooltip(): void
    {
        $html = $this->render(rememberChoiceFeature: true, rememberChoicePerIdp: false, rememberChoiceDuration: '');

        $this->assertStringContainsString('remember_choice', $html);
        $this->assertStringNotContainsString('remember_choice_per_idp', $html);
        $this->assertStringNotContainsString('tooltip__value', $html);
    }

    public function testPerIdpVariantRendersDurationAwareTextAndATooltip(): void
    {
        $html = $this->render(rememberChoiceFeature: true, rememberChoicePerIdp: true, rememberChoiceDuration: '90 days');

        $this->assertStringContainsString('remember_choice_per_idp:90 days', $html);
        $this->assertStringContainsString('wayf__rememberChoice--perIdp', $html);
        $this->assertStringContainsString('class="tooltip__value"', $html);
        $this->assertStringContainsString('remember_choice_per_idp_tooltip:90 days', $html);
    }

    public function testFeatureDisabledRendersNothing(): void
    {
        $html = $this->render(rememberChoiceFeature: false, rememberChoicePerIdp: true, rememberChoiceDuration: '90 days');

        $this->assertSame('', trim($html));
    }

    private function render(bool $rememberChoiceFeature, bool $rememberChoicePerIdp, string $rememberChoiceDuration): string
    {
        $basePath = realpath(__DIR__ . '/../../../../../');

        $filesystemLoader = new FilesystemLoader();
        $filesystemLoader->addPath($basePath . '/theme/skeune/templates/modules', 'theme');
        $filesystemLoader->addPath($basePath . '/theme/base/templates/modules', 'theme');

        $environment = new Environment($filesystemLoader);

        $translator = m::mock(TranslatorInterface::class);
        $translator->shouldReceive('trans')->andReturnUsing(static function ($id, $parameters = []) {
            if (empty($parameters)) {
                return (string) $id;
            }
            return $id . ':' . implode(',', $parameters);
        });
        $environment->addExtension(new TranslationExtension($translator));
        $environment->addExtension(new AttributeExtension(Spaceless::class));

        return $environment->render('@theme/Authentication/View/Proxy/Partials/WAYF/rememberChoice.html.twig', [
            'action' => '/sso',
            'rememberChoiceFeature' => $rememberChoiceFeature,
            'rememberChoicePerIdp' => $rememberChoicePerIdp,
            'rememberChoiceDuration' => $rememberChoiceDuration,
        ]);
    }
}
