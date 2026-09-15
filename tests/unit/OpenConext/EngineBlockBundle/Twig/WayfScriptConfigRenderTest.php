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
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfigurationInterface;
use OpenConext\EngineBlockBundle\Twig\Extensions\Extension\ConnectedIdps;
use OpenConext\EngineBlockBundle\Twig\Extensions\Extension\Spaceless;
use OpenConext\EngineBlockBundle\Twig\Extensions\Extension\Wayf;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Extension\AttributeExtension;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

/**
 * Renders the real `wayf.html.twig` (and the real `scriptConfig.html.twig` it includes) through an
 * actual Twig Environment backed by the FilesystemLoader, using the real `Wayf::getWayfJsonConfig()`
 * Twig function. This exercises the exact `{% include ... with { ... } only %}` context-passing line
 * in `wayf.html.twig` that previously dropped `rememberChoicePerIdp`, so the test fails whenever that
 * include omits the variable, without relying on a mocked `twig->render()` call.
 *
 * To keep the test focused, the `wayf_content` block (IdP list markup: preselection, preferredIdps,
 * remainingIdps, backLink, noAccess, noResults) is overridden to be empty. That markup is unrelated to
 * the script config wiring under test and would otherwise require substantial unrelated setup (IdP list
 * partials, site notice service, request-access forms, etc.) to render.
 */
class WayfScriptConfigRenderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testRememberChoicePerIdpTrueReachesGeneratedScriptConfig(): void
    {
        $config = $this->renderWayfConfig(true);

        $this->assertArrayHasKey('rememberChoicePerIdp', $config);
        $this->assertTrue($config['rememberChoicePerIdp']);
    }

    public function testRememberChoicePerIdpFalseReachesGeneratedScriptConfig(): void
    {
        $config = $this->renderWayfConfig(false);

        $this->assertArrayHasKey('rememberChoicePerIdp', $config);
        $this->assertFalse($config['rememberChoicePerIdp']);
    }

    private function renderWayfConfig(bool $rememberChoicePerIdp): array
    {
        $environment = $this->buildEnvironment();

        $html = $environment->render('test/wayf_wrapper.html.twig', [
            'serviceProvider' => new ServiceProvider('https://sp.example.org', displayNameEn: 'Test SP'),
            'connectedIdps' => new ConnectedIdps([], []),
            'showRequestAccess' => false,
            'rememberChoiceFeature' => true,
            'cutoffPointForShowingUnfilteredIdps' => 5,
            'rememberChoicePerIdp' => $rememberChoicePerIdp,
            'helpLink' => '/authentication/idp/help-discover',
            'displayLanguageSwitcher' => false,
        ]);

        return $this->extractWayfConfig($html);
    }

    private function buildEnvironment(): Environment
    {
        $basePath = realpath(__DIR__ . '/../../../../../');

        $filesystemLoader = new FilesystemLoader();
        $filesystemLoader->addPath($basePath . '/theme/base/templates/modules', 'theme');
        $filesystemLoader->addPath($basePath . '/theme/base/templates/layouts', 'themeLayouts');

        $arrayLoader = new ArrayLoader([
            'test/wayf_wrapper.html.twig' => <<<'TWIG'
{% extends '@theme/Authentication/View/Proxy/wayf.html.twig' %}
{% block wayf_content %}{% endblock %}
TWIG,
        ]);

        $environment = new Environment(new ChainLoader([$arrayLoader, $filesystemLoader]));

        $translator = m::mock(TranslatorInterface::class);
        $translator->shouldReceive('trans')->andReturnUsing(static function ($id) {
            // Needs to be a valid strip_tags() allowlist since warning.html.twig applies it to
            // `allowedHtml|trans`; any other key is simply echoed back.
            return $id === 'consent_warning_allowed_html' ? '<p><strong>' : (string) $id;
        });
        $environment->addExtension(new TranslationExtension($translator));
        $environment->addExtension(new AttributeExtension(Spaceless::class));

        // These are only reachable when `displayLanguageSwitcher` is true, but Twig resolves
        // function calls at compile time, so footer.html.twig (and the language-switcher macro
        // file it imports) still needs them registered even though this test keeps the switcher
        // hidden via `displayLanguageSwitcher: false`.
        $environment->addFunction(new TwigFunction('locale', static fn (): string => 'en'));
        $environment->addFunction(new TwigFunction('postData', static fn (): array => []));
        $environment->addFunction(new TwigFunction('supportedLocales', static fn (): array => ['en']));
        $environment->addFunction(new TwigFunction('queryStringFor', static fn (): string => ''));

        $wayf = new Wayf(
            $this->createStub(RequestStack::class),
            $translator,
            $this->buildFeatureConfiguration(),
        );
        $environment->addFunction(new TwigFunction('wayfConfig', $wayf->getWayfJsonConfig(...)));

        return $environment;
    }

    private function buildFeatureConfiguration(): FeatureConfigurationInterface
    {
        $featureConfiguration = $this->createStub(FeatureConfigurationInterface::class);
        $featureConfiguration->method('isEnabled')->willReturn(false);

        return $featureConfiguration;
    }

    private function extractWayfConfig(string $html): array
    {
        $this->assertMatchesRegularExpression(
            '#<script id="wayf-configuration" type="application/json">(.*?)</script>#s',
            $html,
        );
        preg_match('#<script id="wayf-configuration" type="application/json">(.*?)</script>#s', $html, $matches);

        return json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
    }
}
