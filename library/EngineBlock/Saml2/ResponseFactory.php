<?php
/**
 * NOTE: use for testing only!
 *
 * @todo write test
 */
class EngineBlock_Saml2_ResponseFactory
{
    /**
     * @todo make this generic
     *
     * @param SAML2_AuthnRequest $authnRequest
     * @param SimpleSAML_Configuration $idpConfig
     * @param array $attributes
     * @return SAML2_Response
     */
    public function create(
        SAML2_AuthnRequest $authnRequest,
        SimpleSAML_Configuration $idpConfig,
        $nameId,
        $issuer,
        array $attributes
    )
    {
        /* $returnAttributes contains the attributes we should return. Send them. */
        $assertion = new SAML2_Assertion();
        $assertion->setIssuer($issuer);
        $assertion->setNameId(array(
            'Value' => $nameId,
            'Format' => SAML2_Const::NAMEID_UNSPECIFIED
        ));
        $assertion->setNotBefore(time());
        $assertion->setNotOnOrAfter(time() + 5*60);
        // Valid audiences is not required so disabled for now
        // $assertion->setValidAudiences(array($authnRequest->getIssuer()));
        $assertion->setAttributes($attributes);
        $assertion->setAttributeNameFormat(SAML2_Const::NAMEFORMAT_UNSPECIFIED);
        $assertion->setAuthnContext(SAML2_Const::AC_PASSWORD);

        $subjectConfirmation = new SAML2_XML_saml_SubjectConfirmation();
        $subjectConfirmation->Method = SAML2_Const::CM_BEARER;
        $subjectConfirmation->SubjectConfirmationData = new SAML2_XML_saml_SubjectConfirmationData();
        $subjectConfirmation->SubjectConfirmationData->NotOnOrAfter = time() + 5*60;
        $subjectConfirmation->SubjectConfirmationData->Recipient = $authnRequest->getAssertionConsumerServiceURL();
        $subjectConfirmation->SubjectConfirmationData->InResponseTo = $authnRequest->getId();
        $assertion->setSubjectConfirmation(array($subjectConfirmation));

        $response = new SAML2_Response();
        $response->setRelayState($authnRequest->getRelayState());
        $response->setDestination($authnRequest->getAssertionConsumerServiceURL());
        $response->setIssuer($issuer);
        $response->setInResponseTo($authnRequest->getId());
        $response->setAssertions(array($assertion));

        $this->addSigns($response, $idpConfig);

        return $response;
    }

    /**
     * @param SAML2_Response $response
     * @param SimpleSAML_Configuration $idpConfig
     */
    private function addSigns(SAML2_Response $response, SimpleSAML_Configuration $idpConfig)
    {
        // @todo find out why multiple assertions can exist
        $assertions = $response->getAssertions();
        sspmod_saml_Message::addSign(
            $idpConfig,
            SimpleSAML_Configuration::loadFromArray(array()),
            $assertions[0]
        );
        // Signing of message is not required so disabled for now
        // sspmod_saml_Message::addSign($idpConfig, null, $response);
    }

    /**
     * @param EngineBlock_Http_Request $httpRequest
     * @return SAML2_Message
     */
    public function createFromHttpRequest(EngineBlock_Http_Request $httpRequest)
    {
        $parameter = $this->getParameterFromHttpRequest($httpRequest);
        $responseXml = $this->decodeParameter($parameter);

        $serializer = new EngineBlock_Saml2_MessageSerializer();
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
            throw new Exception('No SAMLResponse parameter');
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
