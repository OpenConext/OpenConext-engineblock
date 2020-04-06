<?php

/**
 * Copyright 2010 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Twig\Extensions\Extension;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlockBundle\Localization\LanguageSupportProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class LocaleTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_get_locale_en()
    {
        $this->assertEquals('en', $this->buildLocale('en')->getLocale());
    }

    public function test_get_locale_falls_back_on_default()
    {
        $this->assertEquals('nl', $this->buildLocale('fr', true)->getLocale());
    }

    public function test_get_supported_locales()
    {
        $this->assertEquals(['en', 'nl'], $this->buildLocale('en', true)->getSupportedLocales());
    }

    public function test_get_supported_locales_with_default_lcoale_fallback()
    {
        $this->assertEquals(['en', 'nl'], $this->buildLocale('fr', true)->getSupportedLocales());
    }

    private function buildLocale($currentLocale = 'en', $unsetCurrentRequest = false)
    {
        $requestStack = m::mock(RequestStack::class);

        if ($unsetCurrentRequest) {
            $requestStack->shouldReceive('getCurrentRequest')->andReturn(null);
        } else {
            $request = m::mock(Request::class);
            $requestStack->shouldReceive('getCurrentRequest')->andReturn($request);

            $request
                ->shouldReceive('getLocale')
                ->andReturn($currentLocale);
        }

        $languageSupportProvider = m::mock(LanguageSupportProvider::class);
        $languageSupportProvider->shouldReceive('getSupportedLanguages')
            ->andReturn(['en', 'nl']);

        return new Locale($requestStack, $languageSupportProvider, 'nl');
    }
}
