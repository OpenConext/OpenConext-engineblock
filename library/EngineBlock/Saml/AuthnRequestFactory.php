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
     * @return SAML2_AuthnRequest
     */
    public function create(
        $destinationUrl,
        $assertionConsumerServiceURL,
        $issuerUrl
    )
    {
        $request = new SAML2_AuthnRequest();
        $request->setDestination($destinationUrl);
        $request->setAssertionConsumerServiceURL($assertionConsumerServiceURL);
        $request->setIssuer($issuerUrl);
        $request->setProtocolBinding(self::BINDING_DEFAULT);
        $request->setNameIdPolicy(array(
            'Format' => self::NAME_ID_FORMAT_DEFAULT,
            'AllowCreate' => true
        ));

        return $request;
    }

    /**
     * @param EngineBlock_Http_Request $httpRequest
     * @return SAML2_AuthnRequest
     */
    public function createFromHttpRequest(EngineBlock_Http_Request $httpRequest)
    {
        $parameter = $this->getParameterFromHttpRequest($httpRequest);
        $requestXml = $this->decodeParameter($parameter);

        $serializer = new EngineBlock_Saml_MessageSerializer();
        return $serializer->deserialize($requestXml, 'SAML2_AuthnRequest');
    }

    /**
     * @param EngineBlock_Http_Request $httpRequest
     * @return string
     * @throws Exception
     */
    private function getParameterFromHttpRequest(EngineBlock_Http_Request $httpRequest)
    {
        $parameter = $httpRequest->getQueryParameter('SAMLRequest');
        if (empty($parameter)) {
            throw new Exception('No SAMLRequest parameter');
        }

        return $parameter;
    }

    /**
     * @param string $parameter
     * @return string
     */
    private function decodeParameter($parameter)
    {
        return gzinflate(base64_decode($parameter));
    }
}