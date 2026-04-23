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

namespace OpenConext\EngineBlockBundle\Tests;

use EngineBlock_ApplicationSingleton;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Exception\MissingParameterException;
use OpenConext\EngineBlock\Service\AuthenticationStateHelperInterface;
use OpenConext\EngineBlock\Service\RequestAccessMailer;
use OpenConext\EngineBlock\Validator\RequestValidator;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfigurationInterface;
use OpenConext\EngineBlockBundle\Controller\IdentityProviderController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Twig\Environment;

class IdentityProviderControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private IdentityProviderController $controller;

    protected function setUp(): void
    {
        $this->controller = new IdentityProviderController(
            Mockery::mock(EngineBlock_ApplicationSingleton::class),
            Mockery::mock(Environment::class),
            Mockery::mock(\Psr\Log\LoggerInterface::class),
            Mockery::mock(RequestAccessMailer::class),
            Mockery::mock(RequestValidator::class),
            Mockery::mock(RequestValidator::class),
            Mockery::mock(RequestValidator::class),
            Mockery::mock(AuthenticationStateHelperInterface::class),
            Mockery::mock(FeatureConfigurationInterface::class)
        );
    }

    #[Test]
    public function a_get_without_saml_request_redirects_to_the_stored_session_url(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set('eb_last_sso_request_url', 'https://engine.example.com/authentication/idp/single-sign-on?SAMLRequest=abc');

        $request = Request::create('https://engine.example.com/authentication/idp/single-sign-on');
        $request->setSession($session);

        $response = $this->controller->singleSignOnAction($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(
            'https://engine.example.com/authentication/idp/single-sign-on?SAMLRequest=abc',
            $response->getTargetUrl()
        );
    }

    #[Test]
    public function the_stored_session_url_is_consumed_on_redirect_so_it_cannot_be_reused(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set('eb_last_sso_request_url', 'https://engine.example.com/authentication/idp/single-sign-on?SAMLRequest=abc');

        $request = Request::create('https://engine.example.com/authentication/idp/single-sign-on');
        $request->setSession($session);

        $this->controller->singleSignOnAction($request);

        $this->assertNull($session->get('eb_last_sso_request_url'));
    }

    #[Test]
    public function a_get_without_saml_request_and_no_stored_session_url_throws_missing_parameter_exception(): void
    {
        $this->expectException(MissingParameterException::class);

        $requestValidator = Mockery::mock(RequestValidator::class);
        $requestValidator->shouldReceive('isValid')->andThrow(new MissingParameterException('The parameter "SAMLRequest" is missing'));

        $controller = new IdentityProviderController(
            Mockery::mock(EngineBlock_ApplicationSingleton::class),
            Mockery::mock(Environment::class),
            Mockery::mock(\Psr\Log\LoggerInterface::class),
            Mockery::mock(RequestAccessMailer::class),
            $requestValidator,
            Mockery::mock(RequestValidator::class),
            Mockery::mock(RequestValidator::class),
            Mockery::mock(AuthenticationStateHelperInterface::class),
            Mockery::mock(FeatureConfigurationInterface::class)
        );

        $session = new Session(new MockArraySessionStorage());
        $request = Request::create('https://engine.example.com/authentication/idp/single-sign-on');
        $request->setSession($session);

        $controller->singleSignOnAction($request);
    }
}
