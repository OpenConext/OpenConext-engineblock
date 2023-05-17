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

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use OpenConext\EngineBlock\Metadata\Coins;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MfaEntityCollection;
use PHPUnit\Framework\TestCase;
use SAML2\Assertion;
use SAML2\AuthnRequest;
use SAML2\Response as SAMLResponse;

class EngineBlock_Test_Corto_Filter_Command_ValidateMfaAuthnContextClassRefTest extends TestCase
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
     * @var EngineBlock_Saml2_AuthnRequestAnnotationDecorator
     */
    private $request;
    private $server;

    public function setUp(): void
    {
        $this->handler = new TestHandler();
        $this->logger  = new Logger('Test', array($this->handler));
        $assertion = new Assertion();

        $request = new AuthnRequest();
        $response = new SAMLResponse();
        $response->setAssertions(array($assertion));

        $this->request = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($request);

        $this->sp = new ServiceProvider('Test SP');
        $this->server = m::mock(EngineBlock_Corto_ProxyServer::class);
        $this->server
            ->shouldReceive('findOriginalServiceProvider')
            ->andReturn($this->sp)
        ;
    }

    public function testNoConfiguredMfaCombinationShouldPass()
    {
        $response = $this->createTestResponse('urn:oasis:names:tc:SAML:2.0:ac:classes:Password');

        $verifier = new EngineBlock_Corto_Filter_Command_ValidateMfaAuthnContextClassRef($this->logger);
        $verifier->setResponse($response);
        $verifier->setIdentityProvider(new IdentityProvider('MFA IdP'));
        $verifier->setServiceProvider($this->sp);
        $verifier->setRequest($this->request);
        $verifier->setProxyServer($this->server);

        $verifier->execute();
    }

    public function testMatchedAuthnContextClassRefShouldPass()
    {
        $response = $this->createTestResponse('http://schemas.microsoft.com/claims/multipleauthn');

        $identityProvider = $this->createConfiguredSpIdpCombination('Test IdP', "Test SP", "http://schemas.microsoft.com/claims/multipleauthn");

        $verifier = new EngineBlock_Corto_Filter_Command_ValidateMfaAuthnContextClassRef($this->logger);
        $verifier->setResponse($response);
        $verifier->setIdentityProvider($identityProvider);
        $verifier->setServiceProvider($this->sp);
        $verifier->setRequest($this->request);
        $verifier->setProxyServer($this->server);

        $verifier->execute();

        $this->assertInstanceOf(EngineBlock_Corto_Filter_Command_Abstract::class, $verifier);
    }

    public function testMatchedAuthnMethodsReferenceAttributeShouldPass()
    {
        $response = $this->createTestResponse('urn:oasis:names:tc:SAML:2.0:ac:classes:Password', ['http://schemas.microsoft.com/claims/multipleauthn']);

        $identityProvider = $this->createConfiguredSpIdpCombination('Test IdP', "Test SP", "http://schemas.microsoft.com/claims/multipleauthn");

        $verifier = new EngineBlock_Corto_Filter_Command_ValidateMfaAuthnContextClassRef($this->logger);
        $verifier->setResponse($response);
        $verifier->setIdentityProvider($identityProvider);
        $verifier->setServiceProvider($this->sp);
        $verifier->setRequest($this->request);
        $verifier->setProxyServer($this->server);

        $verifier->execute();

        $this->assertInstanceOf(EngineBlock_Corto_Filter_Command_Abstract::class, $verifier);
    }

    public function testMatchedAuthnMethodsReferenceAttributeWithMultipleValuesShouldPass()
    {
        $response = $this->createTestResponse('urn:oasis:names:tc:SAML:2.0:ac:classes:Password', [
            'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport',
            'http://schemas.microsoft.com/ws/2012/12/authmethod/phoneappnotification',
            'http://schemas.microsoft.com/claims/multipleauthn',
        ]);

        $identityProvider = $this->createConfiguredSpIdpCombination('Test IdP', "Test SP", "http://schemas.microsoft.com/claims/multipleauthn");

        $verifier = new EngineBlock_Corto_Filter_Command_ValidateMfaAuthnContextClassRef($this->logger);
        $verifier->setResponse($response);
        $verifier->setIdentityProvider($identityProvider);
        $verifier->setServiceProvider($this->sp);
        $verifier->setRequest($this->request);
        $verifier->setProxyServer($this->server);

        $verifier->execute();

        $this->assertInstanceOf(EngineBlock_Corto_Filter_Command_Abstract::class, $verifier);
    }

    public function testMatchedAuthnclassrefAndAuthnMethodsReferenceAttributeWithMultipleValuesShouldPass()
    {
        $response = $this->createTestResponse('http://schemas.microsoft.com/claims/multipleauthn', [
            'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport',
            'http://schemas.microsoft.com/ws/2012/12/authmethod/phoneappnotification',
            'http://schemas.microsoft.com/claims/multipleauthn',
        ]);

        $identityProvider = $this->createConfiguredSpIdpCombination('Test IdP', "Test SP", "http://schemas.microsoft.com/claims/multipleauthn");

        $verifier = new EngineBlock_Corto_Filter_Command_ValidateMfaAuthnContextClassRef($this->logger);
        $verifier->setResponse($response);
        $verifier->setIdentityProvider($identityProvider);
        $verifier->setServiceProvider($this->sp);
        $verifier->setRequest($this->request);
        $verifier->setProxyServer($this->server);

        $verifier->execute();

        $this->assertInstanceOf(EngineBlock_Corto_Filter_Command_Abstract::class, $verifier);
    }

    public function testNotMatchedAuthnContextClassRefShouldThrowException()
    {
        $this->expectException(EngineBlock_Corto_Exception_InvalidMfaAuthnContextClassRef::class);
        $this->expectExceptionMessage('Assertion from MFA IdP "Test IdP" for SP "Test SP" does not contain the requested AuthnContextClassRef "http://schemas.microsoft.com/claims/multipleauthn"');

        $response = $this->createTestResponse('urn:oasis:names:tc:SAML:2.0:ac:classes:Password');

        $identityProvider = new IdentityProvider('Test IdP');

        $this->setCoin($identityProvider, 'mfaEntities', MfaEntityCollection::fromCoin([[
            "entityId" => "Test SP",
            "level" => "http://schemas.microsoft.com/claims/multipleauthn",
        ]]));

        $verifier = new EngineBlock_Corto_Filter_Command_ValidateMfaAuthnContextClassRef($this->logger);
        $verifier->setResponse($response);
        $verifier->setIdentityProvider($identityProvider);
        $verifier->setServiceProvider($this->sp);
        $verifier->setRequest($this->request);
        $verifier->setProxyServer($this->server);

        $verifier->execute();
    }

    public function testNotMatchedAuthnMethodsReferenceAttributeWithMultipleValuesShouldThrowException()
    {
        $this->expectException(EngineBlock_Corto_Exception_InvalidMfaAuthnContextClassRef::class);
        $this->expectExceptionMessage('Assertion from MFA IdP "Test IdP" for SP "Test SP" does not contain the requested AuthnContextClassRef "http://schemas.microsoft.com/claims/multipleauthn"');

        $response = $this->createTestResponse('urn:oasis:names:tc:SAML:2.0:ac:classes:Password', [
            'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport',
            'http://schemas.microsoft.com/ws/2012/12/authmethod/phoneappnotification',
        ]);

        $identityProvider = $this->createConfiguredSpIdpCombination('Test IdP', "Test SP", "http://schemas.microsoft.com/claims/multipleauthn");

        $verifier = new EngineBlock_Corto_Filter_Command_ValidateMfaAuthnContextClassRef($this->logger);
        $verifier->setResponse($response);
        $verifier->setIdentityProvider($identityProvider);
        $verifier->setServiceProvider($this->sp);
        $verifier->setRequest($this->request);
        $verifier->setProxyServer($this->server);

        $verifier->execute();

        $this->assertInstanceOf(EngineBlock_Corto_Filter_Command_Abstract::class, $verifier);
    }

    public function testMatchedTransparentAuthnContextClassRefShouldPass()
    {
        $response = $this->createTestResponse('foobar.example.com');

        $identityProvider = $this->createConfiguredSpIdpCombination('Test IdP', "Test SP", "transparent_authn_context");

        $verifier = new EngineBlock_Corto_Filter_Command_ValidateMfaAuthnContextClassRef($this->logger);
        $verifier->setResponse($response);
        $verifier->setIdentityProvider($identityProvider);
        $verifier->setServiceProvider($this->sp);
        $verifier->setRequest($this->request);
        $verifier->setProxyServer($this->server);

        $verifier->execute();

        $this->assertInstanceOf(EngineBlock_Corto_Filter_Command_Abstract::class, $verifier);
    }

    /**
     * @param string $idpEntity
     * @param string $spEntity
     * @param string $authContextClassRef
     * @return IdentityProvider
     */
    private function createConfiguredSpIdpCombination($idpEntity, $spEntity, $authContextClassRef)
    {
        $identityProvider = new IdentityProvider($idpEntity);
        $this->setCoin($identityProvider, 'mfaEntities', MfaEntityCollection::fromCoin([[
            "entityId" => $spEntity,
            "level" => $authContextClassRef,
        ]]));

        return $identityProvider;
    }

    /**
     * @param $authnContextClassRef
     * @param array $authMethodsReferenceValues
     * @return EngineBlock_Saml2_ResponseAnnotationDecorator
     */
    private function createTestResponse($authnContextClassRef, $authMethodsReferenceValues = [])
    {
        $assertion = new Assertion();
        $assertion->setAuthnContextClassRef($authnContextClassRef);

        if (!empty($authMethodsReferenceValues)) {
            $assertion->setAttributes([
                'http://schemas.microsoft.com/claims/authnmethodsreferences' => $authMethodsReferenceValues,
            ]);
        }

        $response = new SAMLResponse();
        $response->setAssertions(array($assertion));

        return new EngineBlock_Saml2_ResponseAnnotationDecorator($response);
    }

    /**
     * @param AbstractRole $role
     * @param string $key
     * @param string $name
     * @throws ReflectionException
     */
    private function setCoin(AbstractRole $role, $key, $name)
    {
        $jsonData = $role->getCoins()->toJson();
        $data = json_decode($jsonData, true);
        $data[$key] = $name;
        $jsonData = json_encode($data);

        $coins = Coins::fromJson($jsonData);

        $object = new ReflectionClass($role);

        $property = $object->getProperty('coins');
        $property->setAccessible(true);
        $property->setValue($role, $coins);
    }
}
