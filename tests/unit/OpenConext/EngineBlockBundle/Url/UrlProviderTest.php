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

namespace OpenConext\EngineBlockBundle\Url;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlockBundle\Exception\UnableToCreateUrlException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UrlProviderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var m\MockInterface&UrlGeneratorInterface
     */
    private $urlGenerator;

    private $urlProvider;

    protected function setUp(): void
    {
        $this->urlGenerator = m::mock(UrlGeneratorInterface::class);
        $this->urlProvider = new UrlProvider($this->urlGenerator);
    }

    public function test_it_creates_an_absolute_url()
    {
        $this->urlGenerator
            ->shouldReceive('generate')
            ->with('my_route', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ->andReturn('https://example.com/my/route');

        $url = $this->urlProvider->getUrl('my_route', false, null, null);
        $this->assertEquals('https://example.com/my/route', $url);
    }

    public function test_it_adds_key_id_to_the_url_when_processing_mode_is_disabled()
    {
        $this->urlGenerator
            ->shouldReceive('generate')
            ->with('authentication_idp_sso', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ->andReturn('https://example.com/authentication/idp/sso');

        $url = $this->urlProvider->getUrl('authentication_idp_sso', false, 'foobar', null);
        $this->assertEquals('https://example.com/authentication/idp/sso/key:foobar', $url);
    }

    public function test_it_adds_entity_id_to_the_url_when_processing_mode_is_disabled()
    {
        $this->urlGenerator
            ->shouldReceive('generate')
            ->with('authentication_idp_sso', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ->andReturn('https://example.com/authentication/idp/sso');

        $url = $this->urlProvider->getUrl('authentication_idp_sso', false, null, 'https://sp.example.com/metadata');
        $this->assertEquals('https://example.com/authentication/idp/sso/204de9cdc2a24ed982ccc726fb016738', $url);
    }

    public function test_it_adds_key_and_entity_id_to_the_url_when_processing_mode_is_disabled()
    {
        $this->urlGenerator
            ->shouldReceive('generate')
            ->with('authentication_idp_sso', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ->andReturn('https://example.com/authentication/idp/sso');

        $url = $this->urlProvider->getUrl('authentication_idp_sso', false, 'foobar', 'https://sp.example.com/metadata');
        $this->assertEquals(
            'https://example.com/authentication/idp/sso/key:foobar/204de9cdc2a24ed982ccc726fb016738',
            $url
        );
    }

    public function test_it_rejects_unknown_routes()
    {
        $this->urlGenerator
            ->shouldReceive('generate')
            ->with('unknown_route_name', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ->andThrow(RouteNotFoundException::class);

        $this->expectException(UnableToCreateUrlException::class);
        $this->urlProvider->getUrl('unknown_route_name', false, null, null);
    }
}
