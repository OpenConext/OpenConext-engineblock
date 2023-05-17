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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use PHPUnit\Framework\TestCase;
use SAML2\Assertion;
use SAML2\Response;

class EngineBlock_Test_Corto_Filter_Command_ValidateAllowedConnectionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var TestHandler
     */
    private $handler;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var EngineBlock_Saml2_ResponseAnnotationDecorator
     */
    private $response;

    public function setUp(): void
    {
        $this->handler = new TestHandler();
        $this->logger  = new Logger('Test', array($this->handler));

        $assertion = new Assertion();
        $assertion->setAuthnContextClassRef('urn:oasis:names:tc:SAML:2.0:ac:classes:Password');

        $response = new Response();
        $response->setAssertions(array($assertion));

        $this->response = new EngineBlock_Saml2_ResponseAnnotationDecorator($response);
    }

    public function testItShouldRunInNormalConditions()
    {
        $this->expectNotToPerformAssertions();

        $verifier = new EngineBlock_Corto_Filter_Command_ValidateAllowedConnection();
        $verifier->setResponse($this->response);
        $sp = new ServiceProvider('FoobarSP');
        $sp->allowAll = true;
        $verifier->setIdentityProvider(new IdentityProvider('OpenConext'));
        $verifier->setServiceProvider($sp);
        $verifier->execute();
    }

    public function testItShouldRunInNormalConditionsWithTrustedProxy()
    {
        $verifier = new EngineBlock_Corto_Filter_Command_ValidateAllowedConnection();
        $verifier->setResponse($this->response);
        $sp = m::mock(ServiceProvider::class);
        $sp->shouldReceive('isAllowed')->andReturn(true);
        $server = m::mock(EngineBlock_Corto_ProxyServer::class);
        $logger = m::mock(Logger::class);

        $verifier->setProxyServer($server);
        $verifier->setRequest(m::mock(EngineBlock_Saml2_AuthnRequestAnnotationDecorator::class));
        $sp->shouldReceive('getCoins->isTrustedProxy')->andReturn(true);
        $server->shouldReceive('findOriginalServiceProvider')->andReturn($sp);
        $server->shouldReceive('getLogger')->andReturn($logger);
        $verifier->setIdentityProvider(new IdentityProvider('OpenConext'));
        $verifier->setServiceProvider($sp);
        $verifier->execute();
    }

    public function testNotAllowed()
    {
        $verifier = new EngineBlock_Corto_Filter_Command_ValidateAllowedConnection();
        $verifier->setResponse($this->response);
        $sp = new ServiceProvider('FoobarSP');
        $sp->allowAll = false;
        $verifier->setIdentityProvider(new IdentityProvider('OpenConext'));
        $verifier->setServiceProvider($sp);
        self::expectException(EngineBlock_Corto_Exception_InvalidConnection::class);
        self::expectExceptionMessage('Disallowed response by SP configuration. Response from IdP "OpenConext" to SP "FoobarSP"');
        $verifier->execute();
    }
}
