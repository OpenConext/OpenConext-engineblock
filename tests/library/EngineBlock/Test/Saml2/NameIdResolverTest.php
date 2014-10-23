<?php

/**
 * Tests for EngineBlock_Log
 *
 * @group saml2
 */
class EngineBlock_Test_Saml2_NameIdResolverTest extends PHPUnit_Framework_TestCase
{
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
     * @var array
     */
    private $spMetadata;

    public function setUp()
    {
        $this->request = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator(new SAML2_AuthnRequest());

        $assertion = new SAML2_Assertion();
        $assertion->setAttributes(array());
        $response = new SAML2_Response();
        $response->setAssertions(array($assertion));
        $response = new EngineBlock_Saml2_ResponseAnnotationDecorator($response);
        $response->setIntendedNameId('urn:collab:person:example.edu:mock1');
        $this->response = $response;

        $this->spMetadata  = array('EntityID' => 'http://sp.example.edu');
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
        $resolvedNameID = $this->resolver->resolve($this->request, $this->response, $this->spMetadata, $this->collabPersonId);

        // Test
        $this->assertEquals($nameId, $resolvedNameID, 'CustomNameId is used');
    }

    public function testNameIdPolicyInAuthnRequest()
    {
        // Input
        $nameId = array(
            'Format' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
            'Value' => $this->response->getIntendedNameId(),
        );
        /** @var SAML2_AuthnRequest $request */
        $request = $this->request;
        $request->setNameIdPolicy(array('Format' => $nameId['Format']));

        // Run
        $resolvedNameId = $this->resolver->resolve($request, $this->response, $this->spMetadata, $this->collabPersonId);

        // Test
        $this->assertEquals(
            $nameId,
            $resolvedNameId,
            'Assertion NameID is set to unspecified, as requested in the AuthnRequest/NameIDPolicy[Format]'
        );
    }

    public function testNameIdFormatInMetadata()
    {
        // Input
        $nameId = array(
            'Format' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
            'Value' => $this->response->getIntendedNameId(),
        );
        $this->spMetadata['NameIDFormat'] = $nameId['Format'];

        // Run
        $resolvedNameId = $this->resolver->resolve($this->request, $this->response, $this->spMetadata, $this->collabPersonId);

        // Test
        $this->assertEquals(
            $nameId,
            $resolvedNameId,
            'Assertion NameID is set to CustomNameId, allowing overrides in Attribute Manipulations'
        );
    }

    public function testMetadataOverAuthnRequest()
    {
        // Input
        $nameId = array(
            'Format' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
            'Value' => $this->response->getIntendedNameId(),
        );
        $this->spMetadata['NameIDFormat'] = $nameId['Format'];

        /** @var SAML2_AuthnRequest $request */
        $request = $this->request;
        $request->setNameIdPolicy(array('Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient'));

        // Run
        $resolvedNameId = $this->resolver->resolve($this->request, $this->response, $this->spMetadata, $this->collabPersonId);

        // Test
        $this->assertEquals(
            $nameId,
            $resolvedNameId,
            'Assertion NameID is set to what is set for this SP in the Metadata, NOT what it requested'
        );
    }

    public function testPersistent()
    {
        // Input
        $nameId = array(
            'Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
            'Value' => '',
        );
        $this->spMetadata['NameIDFormat'] = $nameId['Format'];

        // Run
        $resolvedNameId = $this->resolver->resolve($this->request, $this->response, $this->spMetadata, $this->collabPersonId);

        // Test
        $this->assertEquals(
            $nameId['Format'],
            $resolvedNameId['Format'],
            'Requesting Persistent gives a persistent identifier'
        );

        // Run
        $resolvedNameId2 = $this->resolver->resolve($this->request, $this->response, $this->spMetadata, $this->collabPersonId);

        // Test
        $this->assertEquals($resolvedNameId, $resolvedNameId2, 'Persistent NameID is persistent');
    }

    public function testTransient()
    {
        global $_SESSION;
        $_SESSION = array();

        // Input
        $nameId = array(
            'Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
            'Value' => '',
        );
        $this->spMetadata['NameIDFormat'] = $nameId['Format'];

        // Run
        $resolvedNameId = $this->resolver->resolve($this->request, $this->response, $this->spMetadata, $this->collabPersonId);

        // Test
        $this->assertEquals(
            $nameId['Format'],
            $resolvedNameId['Format'],
            'Assertion NameID is set to what is set for this SP in the Metadata, NOT what it requested'
        );

        // Run
        $resolvedNameId2 = $this->resolver->resolve($this->request, $this->response, $this->spMetadata, $this->collabPersonId);

        // Test
        $this->assertEquals(
            $resolvedNameId['Value'],
            $resolvedNameId2['Value'],
            'Asking for another NameID in a given session, for the same SP and IdP, gives the same id'
        );

        // Input
        $this->spMetadata['EntityID'] = 'https://sp2.example.edu';

        // Run
        $resolvedNameId3 = $this->resolver->resolve($this->request, $this->response, $this->spMetadata, $this->collabPersonId);

        // Test
        $this->assertNotEquals(
            $resolvedNameId2,
            $resolvedNameId3,
            'Asking for another NameID in a given session, for a different SP, gives a different NameID'
        );

        // Input
        $_SESSION = array();

        // Run
        $resolvedNameId4 = $this->resolver->resolve($this->request, $this->response, $this->spMetadata, $this->collabPersonId);

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
        $nameId = array(
            'Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
            'Value' => '',
        );
        $this->spMetadata['NameIDFormat'] = $nameId['Format'];

        // Run
        $resolvedNameId = $this->resolver->resolve($this->request, $this->response, $this->spMetadata, $this->collabPersonId);

        // Test
        $this->assertNotEmpty($resolvedNameId);
    }
}