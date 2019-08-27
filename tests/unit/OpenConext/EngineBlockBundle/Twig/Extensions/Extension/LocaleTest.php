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
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class LocaleTest extends TestCase
{

    public function test_get_locale_en()
    {
        $this->assertEquals('en', $this->buildLocale('en')->getLocale());
    }

    public function test_get_locale_falls_back_on_default()
    {
        $this->assertEquals('nl', $this->buildLocale('fr', true)->getLocale());
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

        return new Locale($requestStack, 'nl');
    }
}
