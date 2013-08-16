<?php
/**
 * NOTE: use for testing only!
 *
 * @todo write test
 */
class EngineBlock_Saml_AuthnRequestFactory
{
    const BINDING_DEFAULT = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST';
    const NAME_ID_FORMAT_DEFAULT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient';

    /**
     * @param string $destinationUrl
     * @param string $assertionConsumerServiceURL
     * @param string $issuerUrl
     * @return string
     */
    public function create(
        $destinationUrl,
        $assertionConsumerServiceURL,
        $issuerUrl
    )
    {
        $samlpAuthNRequest = new SAML2_AuthnRequest();
        $samlpAuthNRequest->setDestination($destinationUrl);
        $samlpAuthNRequest->setAssertionConsumerServiceURL($assertionConsumerServiceURL);
        $samlpAuthNRequest->setIssuer($issuerUrl);
        $samlpAuthNRequest->setProtocolBinding(self::BINDING_DEFAULT);
        $samlpAuthNRequest->setNameIdPolicy(array(
            'Format' => self::NAME_ID_FORMAT_DEFAULT,
            'AllowCreate' => true
        ));

        return $samlpAuthNRequest;
    }

    /**
     * @param EngineBlock_Http_Request $httpRequest
     * @return SAML2_AuthnRequest
     */
    public function createFromHttpRequest(EngineBlock_Http_Request $httpRequest)
    {
        $samlAuthnrequestParameter = $this->getSAMLAuthnrequestParameterFromHttpRequest($httpRequest);
        $samlAuthnrequest = $this->decodeSAMLAuthnrequestParameter($samlAuthnrequestParameter);

        $samlMessageSerializer = new EngineBlock_Saml_MessageSerializer();
        return $samlMessageSerializer->deserialize($samlAuthnrequest, 'SAML2_AuthnRequest');
    }

    /**
     * @param EngineBlock_Http_Request $httpRequest
     * @return string
     * @throws Exception
     */
    private function getSAMLAuthnrequestParameterFromHttpRequest(EngineBlock_Http_Request $httpRequest)
    {
        $samlAuthnrequestParameter = $httpRequest->getQueryParameter('SAMLRequest');
        if (empty($samlAuthnrequestParameter)) {
            throw new Exception('No SAMLRequest parameter');
        }

        return $samlAuthnrequestParameter;
    }

    /**
     * @param string $samlAuthnrequestParameter
     * @return string
     */
    private function decodeSAMLAuthnrequestParameter($samlAuthnrequestParameter)
    {
        return gzinflate(base64_decode($samlAuthnrequestParameter));
    }
}