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
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use PHPUnit\Framework\TestCase;
use SAML2\Assertion;
use SAML2\AuthnRequest;
use SAML2\Constants;
use SAML2\Response;
use SAML2\XML\saml\NameID;

/**
 * Tests for EngineBlock_Log
 *
 * @group saml2
 */
class EngineBlock_Test_Saml2_NameIdResolverTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var EngineBlock_Saml2_NameIdResolver
     */
    private $resolver;

    /**
     * @var EngineBlock_Saml2_AuthnRequestAnnotationDecorator
     */
    private $request;

    /**
     * @var EngineBlock_Saml2_ResponseAnnotationDecorator
     */
    private $response;

    /**
     * @var string
     */
    private $collabPersonId;

    /**
     * @var ServiceProvider
     */
    private $serviceProvider;

    public function setUp()
    {
        $this->request = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator(new AuthnRequest());

        $assertion = new Assertion();
        $assertion->setAttributes(array());
        $response = new Response();
        $response->setAssertions(array($assertion));
        $response = new EngineBlock_Saml2_ResponseAnnotationDecorator($response);
        $response->setIntendedNameId('urn:collab:person:example.edu:mock1');
        $this->response = $response;

        $this->serviceProvider  = new ServiceProvider('http://sp.example.edu');
        $this->collabPersonId = 'urn:collab:person:example.edu:mock1';

        $this->resolver = new EngineBlock_Test_Saml2_NameIdResolverMock();
    }

    public function testCustomNameId()
    {
        // Input
        $nameId = array(
            'Format' => '',
            'Value' => '',
        );
        $this->response->setCustomNameId($nameId);

        // Run
        $resolvedNameID = $this->resolver->resolve($this->request, $this->response, $this->serviceProvider, $this->collabPersonId);

        // Test
        $this->assertEquals(NameID::fromArray($nameId), $resolvedNameID, 'CustomNameId is used');
    }

    public function testNameIdPolicyInAuthnRequest()
    {
        // Input
        $nameId = array(
            'Format' => Constants::NAMEID_UNSPECIFIED,
            'Value' => $this->response->getIntendedNameId(),
        );
        $this->serviceProvider->supportedNameIdFormats[] = Constants::NAMEID_UNSPECIFIED;
        $request = $this->request;
        $request->setNameIdPolicy(array('Format' => $nameId['Format']));

        // Run
        $resolvedNameId = $this->resolver->resolve($request, $this->response, $this->serviceProvider, $this->collabPersonId);

        // Test
        $this->assertEquals(
            NameID::fromArray($nameId),
            $resolvedNameId,
            'Assertion NameID is set to unspecified, as requested in the AuthnRequest/NameIDPolicy[Format]'
        );
    }

    public function testNameIdFormatInMetadata()
    {
        // Input
        $nameId = array(
            'Format' => Constants::NAMEID_UNSPECIFIED,
            'Value' => $this->response->getIntendedNameId(),
        );
        $this->serviceProvider->nameIdFormat = $nameId['Format'];
        $this->serviceProvider->supportedNameIdFormats[] = Constants::NAMEID_UNSPECIFIED;

        // Run
        $resolvedNameId = $this->resolver->resolve($this->request, $this->response, $this->serviceProvider, $this->collabPersonId);

        // Test
        $this->assertEquals(
            NameID::fromArray($nameId),
            $resolvedNameId,
            'Assertion NameID is set to CustomNameId, allowing overrides in Attribute Manipulations'
        );
    }

    public function testMetadataOverAuthnRequest()
    {
        // Input
        $nameId = array(
            'Format' => Constants::NAMEID_UNSPECIFIED,
            'Value' => $this->response->getIntendedNameId(),
        );
        $this->serviceProvider->nameIdFormat = $nameId['Format'];
        $this->serviceProvider->supportedNameIdFormats[] = Constants::NAMEID_UNSPECIFIED;

        /** @var AuthnRequest $request */
        $request = $this->request;
        $request->setNameIdPolicy(array('Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient'));

        // Run
        $resolvedNameId = $this->resolver->resolve($this->request, $this->response, $this->serviceProvider, $this->collabPersonId);

        // Test
        $this->assertEquals(
            NameID::fromArray($nameId),
            $resolvedNameId,
            'Assertion NameID is set to what is set for this SP in the Metadata, NOT what it requested'
        );
    }

    public function testPersistent()
    {
        $this->markTestSkipped('Fails when switching to other backend, test should not rely on having fixed backend');

        // Input
        $nameId = array(
            'Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
            'Value' => '',
        );
        $this->serviceProvider->nameIdFormat = $nameId['Format'];

        // Run
        $resolvedNameId = $this->resolver->resolve($this->request, $this->response, $this->serviceProvider, $this->collabPersonId);

        // Test
        $this->assertEquals(
            NameID::fromArray($nameId['Format']),
            $resolvedNameId['Format'],
            'Requesting Persistent gives a persistent identifier'
        );

        // Run
        $resolvedNameId2 = $this->resolver->resolve($this->request, $this->response, $this->serviceProvider, $this->collabPersonId);

        // Test
        $this->assertEquals($resolvedNameId, $resolvedNameId2, 'Persistent NameID is persistent');
    }

    public function testTransient()
    {
        global $_SESSION;
        $_SESSION = array();

        // Input
        $this->serviceProvider->nameIdFormat = Constants::NAMEID_TRANSIENT;

        // Run
        $resolvedNameId = $this->resolver->resolve($this->request, $this->response, $this->serviceProvider, $this->collabPersonId);

        // Test
        $this->assertEquals(
            Constants::NAMEID_TRANSIENT,
            $resolvedNameId->Format,
            'Assertion NameID is set to what is set for this SP in the Metadata, NOT what it requested'
        );

        // Run
        $resolvedNameId2 = $this->resolver->resolve($this->request, $this->response, $this->serviceProvider, $this->collabPersonId);

        // Test
        $this->assertEquals(
            $resolvedNameId->value,
            $resolvedNameId2->value,
            'Asking for another NameID in a given session, for the same SP and IdP, gives the same id'
        );

        // Input
        $this->serviceProvider = new ServiceProvider('https://sp2.example.edu');
        $this->serviceProvider->nameIdFormat = Constants::NAMEID_TRANSIENT;

        // Run
        $resolvedNameId3 = $this->resolver->resolve($this->request, $this->response, $this->serviceProvider, $this->collabPersonId);

        // Test
        $this->assertNotEquals(
            $resolvedNameId2,
            $resolvedNameId3,
            'Asking for another NameID in a given session, for a different SP, gives a different NameID'
        );

        // Input
        $_SESSION = array();

        // Run
        $resolvedNameId4 = $this->resolver->resolve($this->request, $this->response, $this->serviceProvider, $this->collabPersonId);

        // Test
        $this->assertNotEquals(
            $resolvedNameId3,
            $resolvedNameId4,
            'Asking for another NameID in a new session, for the same SP and IdP, gives a different NameID'
        );
    }

    public function testNameIDIsAddedAtCorrectLocation()
    {
        global $_SESSION;
        $_SESSION = array();

        // Input
        $nameId = NameID::fromArray([
            'Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
            'Value' => '',
        ]);
        $this->serviceProvider->nameIdFormat = $nameId->Format;

        // Run
        $resolvedNameId = $this->resolver->resolve($this->request, $this->response, $this->serviceProvider, $this->collabPersonId);

        // Test
        $this->assertNotEmpty($resolvedNameId);
    }
}
