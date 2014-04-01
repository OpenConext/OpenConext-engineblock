<?php

class EngineBlock_Test_Corto_Filter_Command_SetNameIdTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EngineBlock_Test_Corto_Filter_Command_SetNameIdMock
     */
    private $_command;

    public function setUp()
    {
        $command = new EngineBlock_Test_Corto_Filter_Command_SetNameIdMock();

        $assertion = new SAML2_Assertion();
        $assertion->setAttributes(array());
        $response = new SAML2_Response();
        $response->setAssertions(array($assertion));
        $response = new EngineBlock_Saml2_ResponseAnnotationDecorator($response);
        $response->setIntendedNameId('urn:collab:person:example.edu:mock1');
        $command->setResponse($response);

        $command->setCollabPersonId('urn:collab:person:example.edu:mock1');
        $command->setRequest(new EngineBlock_Saml2_AuthnRequestAnnotationDecorator(new SAML2_AuthnRequest()));
        $command->setIdpMetadata(array('EntityID' => 'http://idp.example.edu'));
        $command->setSpMetadata(array('EntityID' => 'http://sp.example.edu'));
        $command->setResponseAttributes(array());
        $this->_command = $command;
    }

    public function testCustomNameId()
    {
        // Input
        $command = clone $this->_command;
        $nameId = array(
            'Format' => '',
            'Value' => '',
        );

        $response = $command->getResponse();
        $response->setCustomNameId($nameId);

        // Run
        $command->execute();

        // Output
        $response = $command->getResponse();
        $responseAttributes = $command->getResponseAttributes();

        // Test
        $this->assertEquals(
            $nameId,
            $response->getAssertion()->getNameId(),
            'Assertion NameID is set to CustomNameId, allowing overrides in Attribute Manipulations'
        );
        /** @var DOMNodeList $eppn */
        $eppn = $responseAttributes['urn:mace:dir:attribute-def:eduPersonTargetedID'][0];
        $this->assertEquals(1, $eppn->length, "Only 1 NameID is provided");
        $eppnNode = $eppn->item(0);

        $this->assertEquals(
            $nameId['Value'],
            $eppnNode->attributes->getNamedItem('Value'),
            'CustomNameId is also set in attributes'
        );
    }

    public function testNameIdPolicyInAuthnRequest()
    {
        // Input
        $command = clone $this->_command;

        $response = $command->getResponse();
        $nameId = array(
            'Format' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
            'Value' => $response->getIntendedNameId(),
        );

        $request = $command->getRequest();
        /** @var SAML2_AuthnRequest $request */
        $request->setNameIdPolicy(array('Format' => $nameId['Format']));

        // Run
        $command->execute();

        // Output
        $response = $command->getResponse();

        // Test
        $this->assertEquals(
            $nameId,
            $response->getAssertion()->getNameId(),
            'Assertion NameID is set to unspecified, as requested in the AuthnRequest/NameIDPolicy[Format]'
        );
    }

    public function testNameIdFormatInMetadata()
    {
        // Input
        $command = clone $this->_command;

        $response = $command->getResponse();
        $nameId = array(
            'Format' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
            'Value' => $response->getIntendedNameId(),
        );
        $command->setSpMetadata(
            array_merge_recursive(
                $command->getSpMetadata(),
                array('NameIDFormat' => $nameId['Format'])
            )
        );

        // Run
        $command->execute();

        // Output
        $response = $command->getResponse();

        // Test
        $this->assertEquals(
            $nameId,
            $response->getAssertion()->getNameId(),
            'Assertion NameID is set to CustomNameId, allowing overrides in Attribute Manipulations'
        );
    }

    public function testMetadataOverAuthnRequest()
    {
        // Input
        $command = clone $this->_command;

        $response = $command->getResponse();
        $nameId = array(
            'Format' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
            'Value' => $response->getIntendedNameId(),
        );
        $command->setSpMetadata(
            array_merge_recursive(
                $command->getSpMetadata(),
                array('NameIDFormat' => $nameId['Format'])
            )
        );
        $request = $command->getRequest();
        /** @var SAML2_AuthnRequest $request */
        $request->setNameIdPolicy(array('Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient'));

        // Run
        $command->execute();

        // Output
        $response = $command->getResponse();

        // Test
        $this->assertEquals(
            $nameId,
            $response->getAssertion()->getNameId(),
            'Assertion NameID is set to what is set for this SP in the Metadata, NOT what it requested'
        );
    }

    public function testPersistent()
    {
        // Input
        $command = clone $this->_command;

        $nameId = array(
            'Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
            'Value' => '',
        );
        $command->setSpMetadata(
            array_merge_recursive(
                $command->getSpMetadata(),
                array('NameIDFormat' => $nameId['Format'])
            )
        );

        // Run
        $command->execute();

        // Output
        $firstResponse = $command->getResponse();

        // Test
        $this->assertEquals(
            $nameId['Format'],
            $firstResponse->getNameIdFormat(),
            'Requesting Persistent gives a persistent identifier'
        );

        // Output
        $secondResponse = $command->getResponse();

        // Test
        $this->assertEquals(
            $firstResponse->getNameId(),
            $secondResponse->getNameId(),
            'Persistent NameID is persistent'
        );
    }

    public function testTransient()
    {
        global $_SESSION;
        $_SESSION = array();

        // Input
        $command = clone $this->_command;

        $response = $command->getResponse();
        $nameId = array(
            'Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
            'Value' => '',
        );
        $command->setSpMetadata(
            array_merge_recursive(
                $command->getSpMetadata(),
                array('NameIDFormat' => $nameId['Format'])
            )
        );

        // Run
        $command->execute();

        // Output
        $firstResponse = $command->getResponse();
        $firstResponseNameId = $firstResponse->getNameId();

        // Test
        $this->assertEquals(
            $nameId['Format'],
            $firstResponseNameId['Format'],
            'Assertion NameID is set to what is set for this SP in the Metadata, NOT what it requested'
        );

        // Run
        $command->execute();

        // Output
        $secondResponse = $command->getResponse();
        $secondResponseNameId = $secondResponse->getNameId();

        // Test
        $this->assertEquals(
            $firstResponseNameId['Value'],
            $secondResponseNameId['Value'],
            'Asking for another NameID in a given session, for the same SP and IdP, gives the same id'
        );

        // Input
        $command->setSpMetadata(array_merge($command->getSpMetadata(), array('EntityID' => 'https://sp2.example.edu')));

        // Run
        $command->execute();

        // Output
        $thirdResponse = $command->getResponse();
        $thirdResponseNameId = $thirdResponse->getNameId();

        // Test
        $this->assertNotEquals(
            $secondResponseNameId,
            $thirdResponseNameId,
            'Asking for another NameID in a given session, for a different SP, gives a different NameID'
        );

        // Input
        $_SESSION = array();

        // Run
        $command->execute();

        // Output
        $fourthResponse = $command->getResponse();
        $fourthResponseNameId = $fourthResponse->getNameId();

        // Test
        $this->assertNotEquals(
            $thirdResponseNameId,
            $fourthResponseNameId,
            'Asking for another NameID in a new session, for the same SP and IdP, gives a different NameID'
        );
    }

    public function testNameIDIsAddedAtCorrectLocation()
    {
        global $_SESSION;
        $_SESSION = array();

        // Input
        $command = clone $this->_command;
        $nameId = array(
            'Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
            'Value' => '',
        );

        $command->setSpMetadata(
            array_merge_recursive(
                $command->getSpMetadata(),
                array('NameIDFormat' => $nameId['Format'])
            )
        );

        // Run
        $command->execute();

        // Output
        $outputResponse = $command->getResponse();

        // Test
        $this->assertNotEmpty($outputResponse->getAssertion()->getNameId());
    }

}
