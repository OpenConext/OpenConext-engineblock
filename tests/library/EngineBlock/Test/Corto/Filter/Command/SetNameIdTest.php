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
        $command->setResponse(array('__' => array('IntendedNameId' => 'urn:collab:person:example.edu:mock1')));
        $command->setCollabPersonId('urn:collab:person:example.edu:mock1');
        $command->setRequest(array());
        $command->setIdpMetadata(array('EntityId' => 'http://idp.example.edu'));
        $command->setSpMetadata(array('EntityId' => 'http://sp.example.edu'));
        $command->setResponseAttributes(array());
        $this->_command = $command;
    }

    public function testCustomNameId()
    {
        // Input
        $command = clone $this->_command;
        $nameId = array(
            '_Format' => '',
            '__v'     => '',
        );
        $command->setResponse(array(
            '__' => array(
                'CustomNameId' => $nameId
        )));

        // Run
        $command->execute();

        // Output
        $response = $command->getResponse();
        $responseAttributes = $command->getResponseAttributes();

        // Test
        $this->assertEquals(
            $nameId,
            $response['saml:Assertion']['saml:Subject']['saml:NameID'],
            'Assertion NameID is set to CustomNameId, allowing overrides in Attribute Manipulations'
        );
        $this->assertEquals(
            $nameId,
            $responseAttributes['urn:mace:dir:attribute-def:eduPersonTargetedID'][0]['saml:NameID'],
            'CustomNameId is also set in attributes'
        );
    }

    public function testNameIdPolicyInAuthnRequest()
    {
        // Input
        $command = clone $this->_command;

        $response = $command->getResponse();
        $nameId = array(
            '_Format' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
            '__v' => $response['__']['IntendedNameId'],
        );

        $command->setRequest(
            array_merge_recursive(
                $command->getRequest(),
                array('samlp:NameIDPolicy' => array('_Format' => $nameId['_Format']))));

        // Run
        $command->execute();

        // Output
        $response = $command->getResponse();

        // Test
        $this->assertEquals(
            $nameId,
            $response['saml:Assertion']['saml:Subject']['saml:NameID'],
            'Assertion NameID is set to unspecified, as requested in the AuthnRequest/NameIDPolicy[Format]'
        );
    }

    public function testNameIdFormatInMetadata()
    {
        // Input
        $command = clone $this->_command;

        $response = $command->getResponse();
        $nameId = array(
            '_Format' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
            '__v' => $response['__']['IntendedNameId'],
        );
        $command->setSpMetadata(
            array_merge_recursive(
                $command->getSpMetadata(),
                array('NameIDFormat' => $nameId['_Format'])
            )
        );

        // Run
        $command->execute();

        // Output
        $response = $command->getResponse();

        // Test
        $this->assertEquals(
            $nameId,
            $response['saml:Assertion']['saml:Subject']['saml:NameID'],
            'Assertion NameID is set to CustomNameId, allowing overrides in Attribute Manipulations'
        );
    }

    public function testMetadataOverAuthnRequest()
    {
        // Input
        $command = clone $this->_command;

        $response = $command->getResponse();
        $nameId = array(
            '_Format' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
            '__v' => $response['__']['IntendedNameId'],
        );
        $command->setSpMetadata(
            array_merge_recursive(
                $command->getSpMetadata(),
                array('NameIDFormat' => $nameId['_Format'])
            )
        );
        $command->setRequest(
            array_merge_recursive(
                $command->getRequest(),
                array('samlp:NameIDPolicy' => array('_Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient'))));

        // Run
        $command->execute();

        // Output
        $response = $command->getResponse();

        // Test
        $this->assertEquals(
            $nameId,
            $response['saml:Assertion']['saml:Subject']['saml:NameID'],
            'Assertion NameID is set to what is set for this SP in the Metadata, NOT what it requested'
        );
    }

    public function testPersistent()
    {
        // Input
        $command = clone $this->_command;

        $response = $command->getResponse();
        $nameId = array(
            '_Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
            '__v' => '',
        );
        $command->setSpMetadata(
            array_merge_recursive(
                $command->getSpMetadata(),
                array('NameIDFormat' => $nameId['_Format'])
            )
        );

        // Run
        $command->execute();

        // Output
        $firstResponse = $command->getResponse();

        // Test
        $this->assertEquals(
            $nameId['_Format'],
            $firstResponse['saml:Assertion']['saml:Subject']['saml:NameID']['_Format'],
            'Requesting Persistent gives a persistent identifier'
        );

        // Output
        $secondResponse = $command->getResponse();

        // Test
        $this->assertEquals(
            $firstResponse['saml:Assertion']['saml:Subject']['saml:NameID'],
            $secondResponse['saml:Assertion']['saml:Subject']['saml:NameID'],
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
            '_Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
            '__v' => '',
        );
        $command->setSpMetadata(
            array_merge_recursive(
                $command->getSpMetadata(),
                array('NameIDFormat' => $nameId['_Format'])
            )
        );

        // Run
        $command->execute();

        // Output
        $firstResponse = $command->getResponse();

        // Test
        $this->assertEquals(
            $nameId['_Format'],
            $firstResponse['saml:Assertion']['saml:Subject']['saml:NameID']['_Format'],
            'Assertion NameID is set to what is set for this SP in the Metadata, NOT what it requested'
        );

        // Run
        $command->execute();

        // Output
        $secondResponse = $command->getResponse();

        // Test
        $this->assertEquals(
            $firstResponse['saml:Assertion']['saml:Subject']['saml:NameID']['__v'],
            $secondResponse['saml:Assertion']['saml:Subject']['saml:NameID']['__v'],
            'Asking for another NameID in a given session, for the same SP and IdP, gives the same id'
        );

        // Input
        $command->setSpMetadata(array_merge($command->getSpMetadata(), array('EntityId' => 'https://sp2.example.edu')));

        // Run
        $command->execute();

        // Output
        $thirdResponse = $command->getResponse();

        // Test
        $this->assertNotEquals(
            $secondResponse['saml:Assertion']['saml:Subject']['saml:NameID'],
            $thirdResponse['saml:Assertion']['saml:Subject']['saml:NameID'],
            'Asking for another NameID in a given session, for a different SP, gives a different NameID'
        );

        // Input
        $_SESSION = array();

        // Run
        $command->execute();

        // Output
        $fourthResponse = $command->getResponse();

        // Test
        $this->assertNotEquals(
            $thirdResponse['saml:Assertion']['saml:Subject']['saml:NameID'],
            $fourthResponse['saml:Assertion']['saml:Subject']['saml:NameID'],
            'Asking for another NameID in a new session, for the same SP and IdP, gives a different NameID'
        );
    }

    public function testNameIDIsAddedAtCorrectLocation()
    {
        global $_SESSION;
        $_SESSION = array();

        // Input
        $command = clone $this->_command;

        $inputResponse = array(
            'saml:Assertion' => array(
                'saml:Subject' => array(
                    'saml:SubjectConfirmation' => array()
                )
            )
        );
        $command->setResponse($inputResponse);
        $nameId = array(
            '_Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
            '__v' => '',
        );

        $command->setSpMetadata(
            array_merge_recursive(
                $command->getSpMetadata(),
                array('NameIDFormat' => $nameId['_Format'])
            )
        );

        // Run
        $command->execute();

        // Output
        $outputResponse = $command->getResponse();

        // Test
        $this->assertEquals(
            array('saml:NameID', 'saml:SubjectConfirmation'),
            array_keys($outputResponse['saml:Assertion']['saml:Subject'])
        );
    }

}