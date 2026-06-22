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
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Twig\Environment;

class IdentityProviderControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function buildController(?RequestValidator $requestValidator = null): IdentityProviderController
    {
        return new IdentityProviderController(
            Mockery::mock(EngineBlock_ApplicationSingleton::class),
            Mockery::mock(Environment::class),
            Mockery::mock(LoggerInterface::class),
            Mockery::mock(RequestAccessMailer::class),
            $requestValidator ?? Mockery::mock(RequestValidator::class),
            Mockery::mock(RequestValidator::class),
            Mockery::mock(RequestValidator::class),
            Mockery::mock(AuthenticationStateHelperInterface::class),
            Mockery::mock(FeatureConfigurationInterface::class)
        );
    }

    #[Test]
    public function a_get_without_saml_request_throws_missing_parameter_exception(): void
    {
        $this->expectException(MissingParameterException::class);

        $requestValidator = Mockery::mock(RequestValidator::class);
        $requestValidator->shouldReceive('isValid')->andThrow(new MissingParameterException('The parameter "SAMLRequest" is missing'));

        $session = new Session(new MockArraySessionStorage());
        $request = Request::create('https://engine.example.com/authentication/idp/single-sign-on');
        $request->setSession($session);

        $this->buildController($requestValidator)->singleSignOnAction($request);
    }
}
