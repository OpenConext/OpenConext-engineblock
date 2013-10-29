<?php
/**
 * NOTE: use for testing only!
 *
 * @todo write test
 */
class EngineBlock_Saml2_AuthnRequestFactory
{
    public static function createFromRequest(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $originalRequest,
        array $idpMetadata,
        EngineBlock_Corto_ProxyServer $server
    ) {
        $nameIdPolicy = array('AllowCreate' => 'true');
        /**
         * Name policy is not required, so it is only set if configured, SAML 2.0 spec
         * says only following values are allowed:
         *  - urn:oasis:names:tc:SAML:2.0:nameid-format:transient
         *  - urn:oasis:names:tc:SAML:2.0:nameid-format:persistent.
         *
         * Note: Some IDP's like those using ADFS2 do not understand those, for these cases the format can be 'configured as empty
         * or set to an older version.
         */
        // @todo check why it is empty
        if (!empty($idpMetadata['NameIDFormat'])) {
            $nameIdPolicy['Format'] = $idpMetadata['NameIDFormat'];
        }

        /** @var SAML2_AuthnRequest $originalRequest */

        $sspRequest = new SAML2_AuthnRequest();
        $sspRequest->setId($server->getNewId());
        $sspRequest->setIssueInstant(time());
        $sspRequest->setDestination($idpMetadata['SingleSignOnService'][0]['Location']);
        $sspRequest->setForceAuthn($originalRequest->getForceAuthn());
        $sspRequest->setIsPassive($originalRequest->getIsPassive());
        $sspRequest->setAssertionConsumerServiceURL($server->getUrl('assertionConsumerService'));
        $sspRequest->setProtocolBinding(SAML2_Const::BINDING_HTTP_POST);
        $sspRequest->setIssuer($server->getUrl('spMetadataService'));
        $sspRequest->setNameIdPolicy($nameIdPolicy);

        if (empty($idpMetadata['DisableScoping'])) {
            // Copy over the Idps that are allowed to answer this request.
            $sspRequest->setIDPList($originalRequest->getIDPList());

            // Proxy Count
            $sspRequest->setProxyCount(
                $originalRequest->getProxyCount() ?
                    $originalRequest->getProxyCount() :
                    $server->getConfig('max_proxies', 10)
            );

            // Add the SP to the requesterIds
            $requesterIds = $originalRequest->getRequesterID();
            $requesterIds[] = $originalRequest->getIssuer();

            // Add the SP as the requester
            $sspRequest->setRequesterID($requesterIds);
        }

        // Use the default binding even if more exist
        $request = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($sspRequest);
        $request->setDeliverByBinding($idpMetadata['SingleSignOnService'][0]['Binding']);

        return $request;
    }

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
        $request->setProtocolBinding(SAML2_Const::BINDING_HTTP_POST);
        $request->setNameIdPolicy(array(
            'Format' => SAML2_Const::NAMEID_TRANSIENT,
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

        $serializer = new EngineBlock_Saml2_MessageSerializer();
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
