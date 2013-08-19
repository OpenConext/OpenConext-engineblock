<?php
/**
 * NOTE: use for testing only!
 *
 * @todo write test
 */
class EngineBlock_Saml_ResponseFactory
{
    /**
     * @todo make this generic
     *
     * @param SAML2_AuthnRequest $authnRequest
     * @return SAML2_Response
     */
    public function create(
        SAML2_AuthnRequest $authnRequest
    )
    {
        $sspIdpConfig = array();
        $sspIdpConfig['privatekey'] = ENGINEBLOCK_FOLDER_APPLICATION . 'modules/DummyIdp/keys/private_key.pem';
        $sspIdpConfig['certData'] = file_get_contents(ENGINEBLOCK_FOLDER_APPLICATION . 'modules/DummyIdp/keys/certificate.crt');
        $idpMetadata = new SimpleSAML_Configuration($sspIdpConfig, null);

        $spMetadata = new SimpleSAML_Configuration(array(), null);

        $issuer = $_SERVER['SCRIPT_URI'];

        /* $returnAttributes contains the attributes we should return. Send them. */
        $assertion = new SAML2_Assertion();
        $assertion->setIssuer($issuer);
        // @todo get this from constant
        $assertion->setNameId(array(
            'Value' => 'johndoe',
            'Format' => "urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified"
        ));
        $assertion->setNotBefore(time());
        $assertion->setNotOnOrAfter(time() + 5*60);
        // Valid audiences is not required so disabled for now
        // $assertion->setValidAudiences(array($authnRequest->getIssuer()));

        // Add a few required attributes
        $returnAttributes = array(
            'urn:mace:dir:attribute-def:uid' => array('johndoe'),
            'urn:mace:terena.org:attribute-def:schacHomeOrganization' => array('example.com'),
        );
        $assertion->setAttributes($returnAttributes);
        $assertion->setAttributeNameFormat(SAML2_Const::NAMEFORMAT_UNSPECIFIED);
        $assertion->setAuthnContext(' urn:oasis:names:tc:SAML:2.0:ac:classes:Password');

        $subjectConfirmation = new SAML2_XML_saml_SubjectConfirmation();
        $subjectConfirmation->Method = SAML2_Const::CM_BEARER;
        $subjectConfirmation->SubjectConfirmationData = new SAML2_XML_saml_SubjectConfirmationData();
        $subjectConfirmation->SubjectConfirmationData->NotOnOrAfter = time() + 5*60;
        $subjectConfirmation->SubjectConfirmationData->Recipient = $authnRequest->getAssertionConsumerServiceURL();
        $subjectConfirmation->SubjectConfirmationData->InResponseTo = $authnRequest->getId();
        $assertion->setSubjectConfirmation(array($subjectConfirmation));
        sspmod_saml_Message::addSign($idpMetadata, $spMetadata, $assertion);

        $responseXml = new SAML2_Response();
        $responseXml->setRelayState($authnRequest->getRelayState());
        $responseXml->setDestination($authnRequest->getAssertionConsumerServiceURL());
        $responseXml->setIssuer($issuer);
        $responseXml->setInResponseTo($authnRequest->getId());
        $responseXml->setAssertions(array($assertion));
        // Signing of message is not required so disabled for now
        // sspmod_saml_Message::addSign($idpMetadata, $spMetadata, $responseXml);

        return $responseXml;
    }

    /**
     * @param EngineBlock_Http_Request $httpRequest
     * @return SAML2_Message
     */
    public function createFromHttpRequest(EngineBlock_Http_Request $httpRequest)
    {
        $parameter = $this->getParameterFromHttpRequest($httpRequest);
        $responseXml = $this->decodeParameter($parameter);

        $serializer = new EngineBlock_Saml_MessageSerializer();
        return $serializer->deserialize($responseXml, 'SAML2_Response');
    }

    /**
     * @param EngineBlock_Http_Request $httpRequest
     * @return string
     * @throws Exception
     */
    private function getParameterFromHttpRequest(EngineBlock_Http_Request $httpRequest)
    {
        $parameter = $httpRequest->getPostParameter('SAMLResponse');
        if (empty($parameter)) {
            throw new Exception('No SAMLResponse');
        }

        return $parameter;
    }

    /**
     * @param string $parameter
     * @return string
     */
    private function decodeParameter($parameter)
    {
        return base64_decode($parameter);
    }
}