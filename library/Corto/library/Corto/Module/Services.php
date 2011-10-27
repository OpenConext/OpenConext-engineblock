<?php

require_once 'Abstract.php';

class Corto_Module_Services_Exception extends Corto_ProxyServer_Exception
{
}

class Corto_Module_Services_SessionLostException extends Corto_ProxyServer_Exception
{
}

class Corto_Module_Services extends Corto_Module_Abstract
{
    const DEFAULT_REQUEST_BINDING  = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect';
    const DEFAULT_RESPONSE_BINDING = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST';

    const RESPONSE_CACHE_TYPE_IN  = 'in';
    const RESPONSE_CACHE_TYPE_OUT = 'out';

    // @todo move this to translations?
    const META_TOU_COMMENT = 'Use of this metadata is subject to the Terms of Use at http://www.edugain.org/policy/metadata-tou_1_0.txt';

    /**
     * Handle a Single Sign On request (Authentication Request)
     * @return void
     */
    public function singleSignOnService()
    {
        $request = $this->_server->getBindingsModule()->receiveRequest();
        $request[Corto_XmlToArray::PRIVATE_PFX]['Transparent'] = $this->_server->getCurrentEntitySetting('TransparentProxy', false);

        // The request may specify it ONLY wants a response from specific IdPs
        // or we could have it configured that the SP may only be serviced by specific IdPs
        $scopedIdps = $this->_getScopedIdPs($request);

        $cacheResponseSent = $this->_sendCachedResponse($request, $scopedIdps);
        if ($cacheResponseSent) {
            return;
        }

        // If the scoped proxycount = 0, respond with a ProxyCountExceeded error
        if (isset($request['samlp:Scoping'][Corto_XmlToArray::ATTRIBUTE_PFX . 'ProxyCount']) && $request['samlp:Scoping'][Corto_XmlToArray::ATTRIBUTE_PFX . 'ProxyCount'] == 0) {
            $this->_server->getSessionLog()->debug("SSO: Proxy count exceeded!");
            $response = $this->_server->createErrorResponse($request, 'ProxyCountExceeded');
            $this->_server->sendResponseToRequestIssuer($request, $response);
            return;
        }

        // Get all registered Single Sign On Services
        $candidateIDPs = $this->_server->getIdpEntityIds();

        $this->_server->getSessionLog()->debug(
            "SSO: Candidate idps found in metadata: " . print_r($candidateIDPs, 1)
        );

        // If we have scoping, filter out every non-scoped IdP
        if (count($scopedIdps) > 0) {
            $candidateIDPs = array_intersect($scopedIdps, $candidateIDPs);
        }

        $this->_server->getSessionLog()->debug(
            "SSO: Candidate idps found in metadata after scoping: " . print_r($candidateIDPs, 1)
        );

        // No IdPs found! Send an error response back.
        if (count($candidateIDPs) === 0) {
            $this->_server->getSessionLog()->debug("SSO: No Supported Idps!");
            if ($this->_server->getConfig('NoSupportedIDPError')!=='user') {
                $response = $this->_server->createErrorResponse($request, 'NoSupportedIDP');
                $this->_server->sendResponseToRequestIssuer($request, $response);
                return;
            }
            else {
                $output = $this->_server->renderTemplate(
                    'noidps',
                    array(
                ));
                $this->_server->sendOutput($output);
                return;
            }
        }
        // Exactly 1 candidate found, send authentication request to the first one
        else if (count($candidateIDPs) === 1) {
            $idp = $candidateIDPs[0];
            $this->_server->getSessionLog()->debug("SSO: Only 1 candidate IdP: $idp");
            $this->_server->sendAuthenticationRequest($request, $idp);
            return;
        }
        // Multiple IdPs found...
        else {
            // > 1 IdPs found, but isPassive attribute given, unable to show WAYF
            if (isset($request[Corto_XmlToArray::ATTRIBUTE_PFX . 'IsPassive']) && $request[Corto_XmlToArray::ATTRIBUTE_PFX . 'IsPassive'] === 'true') {
                $this->_server->getSessionLog()->debug("SSO: IsPassive with multiple IdPs!");
                $response = $this->_server->createErrorResponse($request, 'NoPassive');
                $this->_server->sendResponseToRequestIssuer($request, $response);
                return;
            }
            else {
                // Store the request in the session
                $id = $request[Corto_XmlToArray::ATTRIBUTE_PFX . 'ID'];
                $_SESSION[$id]['SAMLRequest'] = $request;

                // Show WAYF
                $this->_server->getSessionLog()->debug("SSO: Showing WAYF");
                $this->_showWayf($request, $candidateIDPs);
                return;
            }
        }
    }

    protected function _sendCachedResponse($request, $scopedIdps)
    {
        if (isset($request[Corto_XmlToArray::ATTRIBUTE_PFX . 'ForceAuthn']) && $request[Corto_XmlToArray::ATTRIBUTE_PFX . 'ForceAuthn']) {
            return false;
        }

        if (!isset($_SESSION['CachedResponses'])) {
            return false;
        }

        $cachedResponses = $_SESSION['CachedResponses'];

        $requestIssuerEntityId  = $request['saml:Issuer'][Corto_XmlToArray::VALUE_PFX];

        // First, if there is scoping, we reject responses from idps not in the list
        if (count($scopedIdps) > 0) {
            foreach ($cachedResponses as $key => $cachedResponse) {
                if (!in_array($cachedResponse['idp'], $scopedIdps)) {
                    unset($cachedResponses[$key]);
                }
            }
        }
        if (empty($cachedResponses)) {
            return false;
        }

        $cachedResponse = $this->_pickCachedResponse($cachedResponses, $request, $requestIssuerEntityId);
        if (!$cachedResponse) {
            return false;
        }

        if ($cachedResponse['type'] === self::RESPONSE_CACHE_TYPE_OUT) {
            $this->_server->getSessionLog()->debug("SSO: Cached response found for SP");
            $response = $this->_server->createEnhancedResponse($request, $cachedResponse['response']);
            $this->_server->sendResponseToRequestIssuer($request, $response);
        }
        else {
            $this->_server->getSessionLog()->debug("SSO: Cached response found from Idp");
            // Note that we would like to repurpose the response,
            // but that's tricky as it is probably no longer valid (lifetime is usually something like 5 minutes)
            // so instead we scope the request to that Idp and trust the Idp to do the remembering.
            $this->_server->sendAuthenticationRequest($request, $cachedResponse['idp']);
        }
        return true;
    }

    protected function _pickCachedResponse(array $cachedResponses, array $request, $requestIssuerEntityId)
    {
        // Then we look for OUT responses for this sp

        $idpEntityIds = $this->_server->getIdpEntityIds();
        foreach ($cachedResponses as $cachedResponse) {
            if ($cachedResponse['type'] !== self::RESPONSE_CACHE_TYPE_OUT) {
                continue;
            }

            // Check if it is for the requester
            if ($cachedResponse['sp'] !== $requestIssuerEntityId) {
                continue;
            }

            // Check if it is for a valid idp
            if (!in_array($cachedResponse['idp'], $idpEntityIds)) {
                continue;
            }

            return $cachedResponse;
        }

        // Then we look for IN responses for this sp
        foreach ($cachedResponses as $cachedResponse) {
            if ($cachedResponse['type'] !== self::RESPONSE_CACHE_TYPE_IN) {
                continue;
            }

            // Check if it is for a valid idp
            if (!in_array($cachedResponse['idp'], $idpEntityIds)) {
                continue;
            }

            return $cachedResponse;
        }

        return false;
    }

    protected function _getScopedIdPs($request = null)
    {
        $scopedIdPs = array();
        // Add scoped IdPs (allowed IDPs for reply) from request to allowed IdPs for responding
        if (isset($request['samlp:Scoping']['samlp:IDPList']['samlp:IDPEntry'])) {
            foreach ($request['samlp:Scoping']['samlp:IDPList']['samlp:IDPEntry'] as $IDPEntry) {
                $scopedIdPs[] = $IDPEntry[Corto_XmlToArray::ATTRIBUTE_PFX . 'ProviderID'];
            }
            $this->_server->getSessionLog()->debug("SSO: Request contains scoped idps: " . print_r($scopedIdPs, 1));
        }

        $presetIdPs = $this->_server->getCurrentEntitySetting('IDPList');
        $presetIdP  = $this->_server->getCurrentEntitySetting('Idp');

        // If we have ONE specific IdP pre-configured then we scope to ONLY that Idp
        if ($presetIdP) {
            $scopedIdPs = array($presetIdP);
            $this->_server->getSessionLog()->debug("SSO: Scoped idp found in metadata: " . $scopedIdPs[0]);
        }
        // If we configured an IDPList it overrides the one in the request
        else if ($presetIdPs) {
            $scopedIdPs = $presetIdPs;
            $this->_server->getSessionLog()->debug("SSO: Scoped idps found in metadata: " . print_r($scopedIdPs, 1));
        }
        return $scopedIdPs;
    }

    /**
     * Handle the forwarding of the user to the proper IdP0 after the WAYF screen.
     *
     * @return void
     */
    public function continueToIdP()
    {
        $selectedIdp = urldecode($_REQUEST['idp']);
        if (!$selectedIdp) {
            throw new Corto_Module_Services_Exception('No IdP selected after WAYF');
        }

        // Retrieve the request from the session.
        $id      = $_POST['ID'];
        if (!isset($_SESSION[$id]['SAMLRequest'])) {
            throw new Corto_Module_Services_SessionLostException('Session lost after WAYF');
        }
        $request = $_SESSION[$id]['SAMLRequest'];

        $this->_server->sendAuthenticationRequest($request, $selectedIdp);
    }

    /**
     * Receive the assertion from the IdP and send it on to the SP.
     *
     * @throws Corto_Module_Services_Exception
     * @return void
     */
    public function assertionConsumerService()
    {
        $receivedResponse = $this->_server->getBindingsModule()->receiveResponse();

        // Get the ID of the Corto Request message
        if (!$receivedResponse[Corto_XmlToArray::ATTRIBUTE_PFX . 'InResponseTo']) {
            $message = "Unsollicited assertion (no InResponseTo in message) not supported!";
            throw new Corto_Module_Services_Exception($message);
        }

        $receivedRequest = $this->_server->getReceivedRequestFromResponse($receivedResponse[Corto_XmlToArray::ATTRIBUTE_PFX . 'InResponseTo']);

        // Cache the response
        if ($this->_server->getCurrentEntitySetting('keepsession', false)) {
            $this->_cacheResponse($receivedRequest, $receivedResponse, self::RESPONSE_CACHE_TYPE_IN);
        }

        $this->_server->filterInputAssertionAttributes($receivedResponse, $receivedRequest);

        $processingEntities = $this->_getReceivedResponseProcessingEntities($receivedRequest, $receivedResponse);
        if (!empty($processingEntities)) {
            $firstProcessingEntity = array_shift($processingEntities);
            $_SESSION['Processing'][$receivedRequest[Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['RemainingEntities']   = $processingEntities;
            $_SESSION['Processing'][$receivedRequest[Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['OriginalDestination'] = $receivedResponse[Corto_XmlToArray::ATTRIBUTE_PFX . 'Destination'];
            $_SESSION['Processing'][$receivedRequest[Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['OriginalIssuer']      = $receivedResponse['saml:Assertion']['saml:Issuer'][Corto_XmlToArray::VALUE_PFX];
            $_SESSION['Processing'][$receivedRequest[Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['OriginalBinding']     = $receivedResponse[Corto_XmlToArray::PRIVATE_PFX]['ProtocolBinding'];

            $this->_server->setProcessingMode();
            $newResponse = $this->_server->createEnhancedResponse($receivedRequest, $receivedResponse);

            // Change the destiny of the received response
            $newResponse[Corto_XmlToArray::ATTRIBUTE_PFX . 'InResponseTo']          = $receivedResponse[Corto_XmlToArray::ATTRIBUTE_PFX . 'InResponseTo'];
            $newResponse[Corto_XmlToArray::ATTRIBUTE_PFX . 'Destination']           = $firstProcessingEntity['Location'];
            $newResponse[Corto_XmlToArray::PRIVATE_PFX]['ProtocolBinding']  = $firstProcessingEntity['Binding'];
            $newResponse[Corto_XmlToArray::PRIVATE_PFX]['Return']           = $this->_server->getCurrentEntityUrl('processedAssertionConsumerService');
            $newResponse[Corto_XmlToArray::PRIVATE_PFX]['paramname']        = 'SAMLResponse';

            $responseAssertionAttributes = &$newResponse['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute'];
            $attributes = Corto_XmlToArray::attributes2array($responseAssertionAttributes);
            $attributes['ServiceProvider'] = array($receivedRequest['saml:Issuer'][Corto_XmlToArray::VALUE_PFX]);
            $responseAssertionAttributes = Corto_XmlToArray::array2attributes($attributes);

            $this->_server->getBindingsModule()->send($newResponse, $firstProcessingEntity);
        }
        else {
            // Cache the response
            if ($this->_server->getCurrentEntitySetting('keepsession', false)) {
                $this->_cacheResponse($receivedRequest, $receivedResponse, self::RESPONSE_CACHE_TYPE_OUT);
            }

            $newResponse = $this->_server->createEnhancedResponse($receivedRequest, $receivedResponse);
            $this->_server->sendResponseToRequestIssuer($receivedRequest, $newResponse);
        }
    }

    protected function _cacheResponse(array $receivedRequest, array $receivedResponse, $type)
    {
        $requestIssuerEntityId  = $receivedRequest['saml:Issuer'][Corto_XmlToArray::VALUE_PFX];
        $responseIssuerEntityId = $receivedResponse['saml:Issuer'][Corto_XmlToArray::VALUE_PFX];
        if (!isset($_SESSION['CachedResponses'])) {
            $_SESSION['CachedResponses'] = array();
        }
        $_SESSION['CachedResponses'][] = array(
            'sp'            => $requestIssuerEntityId,
            'idp'           => $responseIssuerEntityId,
            'type'          => $type,
            'response'      => $receivedResponse,
        );
        return $_SESSION['CachedResponses'][count($_SESSION['CachedResponses']) - 1];
    }

    protected function _getReceivedResponseProcessingEntities(array $receivedRequest, array $receivedResponse)
    {
        $currentEntityProcessing = $this->_server->getCurrentEntitySetting('Processing', array());

        $remoteEntity = $this->_server->getRemoteEntity($receivedRequest['saml:Issuer'][Corto_XmlToArray::VALUE_PFX]);

        $processing = $currentEntityProcessing;
        if (isset($remoteEntity['Processing'])) {
            $processing += $remoteEntity['Processing'];
        }

        return $processing;
    }

    /**
     * Ask the user for consent over all of the attributes being sent to the SP.
     *
     * Note this is part 1/2 of the Corto Consent Internal Response Processing service.
     *
     * @return void
     */
    public function provideConsentService()
    {
        $response = $this->_server->getBindingsModule()->receiveResponse();
        $_SESSION['consent'][$response[Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['response'] = $response;

        $attributes = Corto_XmlToArray::attributes2array(
                $response['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute']
        );
        $serviceProviderEntityId = $attributes['ServiceProvider'][0];
        unset($attributes['ServiceProvider']);

        $priorConsent = $this->_hasStoredConsent($serviceProviderEntityId, $response, $attributes);
        if ($priorConsent) {
            $response[Corto_XmlToArray::ATTRIBUTE_PFX . 'Consent'] = 'urn:oasis:names:tc:SAML:2.0:consent:prior';

            $response[Corto_XmlToArray::ATTRIBUTE_PFX . 'Destination'] = $response[Corto_XmlToArray::PRIVATE_PFX]['Return'];
            $response[Corto_XmlToArray::PRIVATE_PFX]['ProtocolBinding'] = self::DEFAULT_RESPONSE_BINDING;

            $this->_server->getBindingsModule()->send(
                $response,
                $this->_server->getRemoteEntity($serviceProviderEntityId)
            );
            return;
        }
        $html = $this->_server->renderTemplate(
                'consent',
                array(
                    'action'        => $this->_server->getCurrentEntityUrl('processConsentService'),
                    'ID'            => $response[Corto_XmlToArray::ATTRIBUTE_PFX . 'ID'],
                    'attributes'    => $attributes,
        ));
        $this->_server->sendOutput($html);
    }

    /**
     * Process consent that is given and sent the user back to the proxy flow
     *
     * Note this is part 2/2 of the Corto Consent Response Processing service.
     *
     * @return void
     */
    public function processConsentService()
    {
        if (!isset($_SESSION['consent'])) {
            throw new Corto_Module_Services_SessionLostException('Session lost after consent');
        }
        if (!isset($_SESSION['consent'][$_POST['ID']]['response'])) {
            throw new Corto_Module_Services_Exception("Stored response for ResponseID '{$_POST['ID']}' not found");
        }
        $response = $_SESSION['consent'][$_POST['ID']]['response'];

        $attributes = Corto_XmlToArray::attributes2array(
                $response['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute']
        );
        $serviceProviderEntityId = $attributes['ServiceProvider'][0];
        unset($attributes['ServiceProvider']);

        if (!isset($_POST['consent']) || $_POST['consent'] !== 'yes') {
            // No consent given
            print $this->_server->renderTemplate(
                'noconsent',
                array(
                    'attributes' => $attributes,
                )
            );
            return;
        }

        $this->_storeConsent($serviceProviderEntityId, $response, $attributes);

        $response[Corto_XmlToArray::ATTRIBUTE_PFX . 'Consent'] = 'urn:oasis:names:tc:SAML:2.0:consent:obtained';
        $response[Corto_XmlToArray::ATTRIBUTE_PFX . 'Destination'] = $response[Corto_XmlToArray::PRIVATE_PFX]['Return'];
        $response[Corto_XmlToArray::PRIVATE_PFX]['ProtocolBinding'] = self::DEFAULT_RESPONSE_BINDING;

        $this->_server->getBindingsModule()->send(
            $response,
            $this->_server->getRemoteEntity($serviceProviderEntityId)
        );
    }

    /**
     *
     * @return void
     */
    public function processedAssertionConsumerService()
    {
        $response = $this->_server->getBindingsModule()->receiveResponse();
        $receivedRequest = $this->_server->getReceivedRequestFromResponse($response[Corto_XmlToArray::ATTRIBUTE_PFX . 'InResponseTo']);

        $remainingProcessingEntities = &$_SESSION['Processing'][$receivedRequest[Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['RemainingEntities'];

        if (!empty($remainingProcessingEntities)) { // Moar processing!
            $nextProcessingEntity = array_shift($remainingProcessingEntities);

            $this->_server->setProcessingMode();

            $newResponse = $this->_server->createEnhancedResponse($receivedRequest, $response);

            // Change the destiny of the received response
            $newResponse[Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']                    = $response[Corto_XmlToArray::ATTRIBUTE_PFX . 'ID'];
            $newResponse[Corto_XmlToArray::ATTRIBUTE_PFX . 'Destination']           = $nextProcessingEntity['Location'];
            $newResponse[Corto_XmlToArray::PRIVATE_PFX]['ProtocolBinding']  = $nextProcessingEntity['Binding'];
            $newResponse[Corto_XmlToArray::PRIVATE_PFX]['Return']           = $this->_server->getCurrentEntityUrl('processedAssertionConsumerService');
            $newResponse[Corto_XmlToArray::PRIVATE_PFX]['paramname']        = 'SAMLResponse';

            $this->_server->getBindingsModule()->send($newResponse, $nextProcessingEntity);
            return;
        }
        else { // Done processing! Send off to SP
            $response[Corto_XmlToArray::ATTRIBUTE_PFX . 'Destination']          = $_SESSION['Processing'][$receivedRequest[Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['OriginalDestination'];
            $response[Corto_XmlToArray::PRIVATE_PFX]['ProtocolBinding'] = $_SESSION['Processing'][$receivedRequest[Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['OriginalBinding'];
            $response[Corto_XmlToArray::PRIVATE_PFX]['OriginalIssuer']  = $_SESSION['Processing'][$receivedRequest[Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['OriginalIssuer'];

            $responseAssertionAttributes = &$response['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute'];
            $attributes = Corto_XmlToArray::attributes2array($responseAssertionAttributes);
            unset($attributes['ServiceProvider']);
            $responseAssertionAttributes = Corto_XmlToArray::array2attributes($attributes);

            $this->_server->unsetProcessingMode();

            // Cache the response
            if ($this->_server->getCurrentEntitySetting('keepsession', false)) {
                $this->_cacheResponse($receivedRequest, $response, self::RESPONSE_CACHE_TYPE_OUT);
            }

            $sentResponse = $this->_server->createEnhancedResponse($receivedRequest, $response);
            $this->_server->sendResponseToRequestIssuer($receivedRequest, $sentResponse);
            return;
        }
    }

    /**
     * Describes Corto as an IdP to SPs
     *
     * @throws Exception
     * @return void
     */
    public function idPMetadataService()
    {
        // Fetch SP Entity Descriptor for the SP Entity ID that is fetched from the request
        $request = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest();
        $spEntityId = $request->getQueryParameter('sp-entity-id');
        if ($spEntityId) {
            $spEntity = $this->_server->getRemoteEntity($spEntityId);
        }

        $entityDescriptor = array(
            Corto_XmlToArray::TAG_NAME_PFX => 'md:EntityDescriptor',
            Corto_XmlToArray::COMMENT_PFX => self::META_TOU_COMMENT,
            Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:md' => 'urn:oasis:names:tc:SAML:2.0:metadata',
            Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:mdui' => 'urn:oasis:names:tc:SAML:2.0:metadata:ui',
            Corto_XmlToArray::ATTRIBUTE_PFX . 'validUntil' => $this->_server->timeStamp($this->_server->getCurrentEntitySetting(
                                                           'idpMetadataValidUntilSeconds', 86400)),
            Corto_XmlToArray::ATTRIBUTE_PFX . 'entityID' => $this->_server->getCurrentEntityUrl('idPMetadataService'),
            Corto_XmlToArray::ATTRIBUTE_PFX . 'ID' => $this->_server->getNewId(),
            'ds:Signature' => Corto_XmlToArray::PLACEHOLDER_VALUE,
            'md:IDPSSODescriptor' => array(
                Corto_XmlToArray::ATTRIBUTE_PFX . 'protocolSupportEnumeration' => "urn:oasis:names:tc:SAML:2.0:protocol",
            ),
        );

        $voContext = $this->_server->getVirtualOrganisationContext();
        $this->_server->setVirtualOrganisationContext(null);
        $canonicalIdpEntityId = $this->_server->getCurrentEntityUrl('idPMetadataService');
        $this->_server->setVirtualOrganisationContext($voContext);

        $entityDetails = $this->_server->getRemoteEntity($canonicalIdpEntityId);

        $this->_addContactPersonsToEntityDescriptor($entityDescriptor, $entityDetails);

        $this->_addDisplayNamesToEntityDescriptor($entityDescriptor['md:IDPSSODescriptor'], $entityDetails);

        // Check if an alternative Public & Private key have been set for a SP
        // If yes, use these in the metadata of Engineblock
        if (isset($spEntity)
            && $spEntity['AlternatePrivateKey']
            && $spEntity['AlternatePublicKey']
        ) {
            $publicCertificate = $spEntity['AlternatePublicKey'];
        } else {
            $certificates = $this->_server->getCurrentEntitySetting('certificates', array());
            $publicCertificate = $certificates['public'];
        }

        if (isset($publicCertificate)) {
            $entityDescriptor['md:IDPSSODescriptor']['md:KeyDescriptor'] = array(
                array(
                    Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                    Corto_XmlToArray::ATTRIBUTE_PFX . 'use' => 'signing',
                    'ds:KeyInfo' => array(
                        'ds:X509Data' => array(
                            'ds:X509Certificate' => array(
                                Corto_XmlToArray::VALUE_PFX => $this->_server->getCertDataFromPem($publicCertificate),
                            ),
                        ),
                    ),
                ),
                array(
                    Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                    Corto_XmlToArray::ATTRIBUTE_PFX . 'use' => 'encryption',
                    'ds:KeyInfo' => array(
                        'ds:X509Data' => array(
                            'ds:X509Certificate' => array(
                                Corto_XmlToArray::VALUE_PFX => $this->_server->getCertDataFromPem($publicCertificate),
                            ),
                        ),
                    ),
                    'md:EncryptionMethod' => array(
                        array(
                            '__v' => 'http://www.w3.org/2001/04/xmlenc#rsa-1_5',
                        ),
                    ),
                ),
                array(
                    Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                    'ds:KeyInfo' => array(
                        'ds:X509Data' => array(
                            'ds:X509Certificate' => array(
                                Corto_XmlToArray::VALUE_PFX => $this->_loadHostSslKey(),
                            ),
                        ),
                    ),
                ),
            );
        }
        $entityDescriptor['md:IDPSSODescriptor']['md:NameIDFormat'] = array(
            Corto_XmlToArray::VALUE_PFX => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
        );
        $entityDescriptor['md:IDPSSODescriptor']['md:SingleSignOnService'] = array(
            Corto_XmlToArray::ATTRIBUTE_PFX . 'Binding'  => self::DEFAULT_REQUEST_BINDING,
            Corto_XmlToArray::ATTRIBUTE_PFX . 'Location' => $this->_server->getCurrentEntityUrl('singleSignOnService'),
        );

        $entityDescriptor = $this->_server->sign(
            $entityDescriptor,
            (isset($spEntity['AlternatePublicKey'])  ? $spEntity['AlternatePublicKey']  : null),
            (isset($spEntity['AlternatePrivateKey']) ? $spEntity['AlternatePrivateKey'] : null)
        );
        $xml = Corto_XmlToArray::array2xml($entityDescriptor);

        $this->_validateXml($xml);

        $this->_server->sendHeader('Content-Type', 'application/xml');
        //$this->_server->sendHeader('Content-Type', 'application/samlmetadata+xml');
        $this->_server->sendOutput($xml);
    }

    /**
     * Describes Corto as an SP to IdPs
     *
     * @throws Exception
     * @return void
     */
    public function sPMetadataService()
    {
        $spEntityId = $this->_server->getCurrentEntityUrl('sPMetadataService');

        $entityDescriptor = array(
            Corto_XmlToArray::TAG_NAME_PFX => 'md:EntityDescriptor',
            Corto_XmlToArray::COMMENT_PFX => self::META_TOU_COMMENT,
            Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:md' => 'urn:oasis:names:tc:SAML:2.0:metadata',
            Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:mdui' => 'urn:oasis:names:tc:SAML:2.0:metadata:ui',
            Corto_XmlToArray::ATTRIBUTE_PFX . 'validUntil' => $this->_server->timeStamp(
                $this->_server->getCurrentEntitySetting('idpMetadataValidUntilSeconds', 86400)
            ),
            Corto_XmlToArray::ATTRIBUTE_PFX . 'entityID' => $spEntityId,
            Corto_XmlToArray::ATTRIBUTE_PFX . 'ID' => $this->_server->getNewId(),
            'ds:Signature' => Corto_XmlToArray::PLACEHOLDER_VALUE,
            'md:SPSSODescriptor' => array(
                Corto_XmlToArray::ATTRIBUTE_PFX . 'protocolSupportEnumeration' => "urn:oasis:names:tc:SAML:2.0:protocol",
            ),
        );

        $voContext = $this->_server->getVirtualOrganisationContext();
        $this->_server->setVirtualOrganisationContext(null);
        $canonicalSpEntityId = $this->_server->getCurrentEntityUrl('sPMetadataService');
        $this->_server->setVirtualOrganisationContext($voContext);

        $entityDetails = $this->_server->getRemoteEntity($canonicalSpEntityId);

        $this->_addContactPersonsToEntityDescriptor($entityDescriptor, $entityDetails);

        $this->_addDisplayNamesToEntityDescriptor($entityDescriptor['md:SPSSODescriptor'], $entityDetails);

        $certificates = $this->_server->getCurrentEntitySetting('certificates', array());
        if (isset($certificates['public'])) {
            $entityDescriptor['md:SPSSODescriptor']['md:KeyDescriptor'] = array(
                array(
                    Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                    Corto_XmlToArray::ATTRIBUTE_PFX . 'use' => 'signing',
                    'ds:KeyInfo' => array(
                        'ds:X509Data' => array(
                            'ds:X509Certificate' => array(
                                Corto_XmlToArray::VALUE_PFX => $this->_server->getCertDataFromPem($certificates['public']),
                            ),
                        ),
                    ),
                ),
                array(
                    Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                    Corto_XmlToArray::ATTRIBUTE_PFX . 'use' => 'encryption',
                    'ds:KeyInfo' => array(
                        'ds:X509Data' => array(
                            'ds:X509Certificate' => array(
                                Corto_XmlToArray::VALUE_PFX => $this->_server->getCertDataFromPem($certificates['public']),
                            ),
                        ),
                    ),
                ),
                array(
                    Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                    'ds:KeyInfo' => array(
                        'ds:X509Data' => array(
                            'ds:X509Certificate' => array(
                                Corto_XmlToArray::VALUE_PFX => $this->_loadHostSslKey(),
                            ),
                        ),
                    ),
                ),
            );
        }

        $entityDescriptor['md:SPSSODescriptor']['md:NameIDFormat'] = array(
            Corto_XmlToArray::VALUE_PFX => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient'
        );
        $entityDescriptor['md:SPSSODescriptor']['md:AssertionConsumerService'] = array(
            Corto_XmlToArray::ATTRIBUTE_PFX . 'Binding'  => self::DEFAULT_RESPONSE_BINDING,
            Corto_XmlToArray::ATTRIBUTE_PFX . 'Location' => $this->_server->getCurrentEntityUrl('assertionConsumerService'),
            Corto_XmlToArray::ATTRIBUTE_PFX . 'index' => '1',
        );

        $entityDescriptor['md:SPSSODescriptor']['md:AttributeConsumingService'] = array(
            // @todo get correct value for index
            Corto_XmlToArray::ATTRIBUTE_PFX . 'index' => 1,
         );

        $this->_addServiceNamesToAttributeConsumingService(
            $entityDescriptor['md:SPSSODescriptor']['md:AttributeConsumingService'], $entityDetails);

        $this->_addServiceDescriptionsToAttributeConsumingService(
            $entityDescriptor['md:SPSSODescriptor']['md:AttributeConsumingService'], $entityDetails);

        $entityDescriptor['md:SPSSODescriptor']['md:AttributeConsumingService']['md:RequestedAttribute'] = array(
            // Mail (example: john@surfnet.nl)
            array(
                Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:mace:dir:attribute-def:mail'
            ),
            array(
                Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:oid:0.9.2342.19200300.100.1.3',
                Corto_XmlToArray::ATTRIBUTE_PFX . 'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
                Corto_XmlToArray::ATTRIBUTE_PFX . 'isRequired' => 'true'
            ),

            // DisplayName (example: John Doe)
            array(
                Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:mace:dir:attribute-def:displayName'
            ),
            array(
                Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:oid:2.16.840.1.113730.3.1.241',
                Corto_XmlToArray::ATTRIBUTE_PFX . 'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
                Corto_XmlToArray::ATTRIBUTE_PFX . 'isRequired' => 'true'
            ),

            // Surname (example: Doe)
            array(
                Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:mace:dir:attribute-def:sn'
            ),
            array(
                Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:oid:2.5.4.4',
                Corto_XmlToArray::ATTRIBUTE_PFX . 'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
                Corto_XmlToArray::ATTRIBUTE_PFX . 'isRequired' => 'true'
            ),

            // Given name (example: John)
            array(
                Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:mace:dir:attribute-def:givenName'
            ),
            array(
                Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:oid:2.5.4.42',
                Corto_XmlToArray::ATTRIBUTE_PFX . 'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
                Corto_XmlToArray::ATTRIBUTE_PFX . 'isRequired' => 'true'
            ),

            // SchachomeOrganization
            array(
                Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:mace:terena.org:schac:homeOrganization'
            ),
            array(
                Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:oid:1.3.6.1.4.1.25178.1.2.9'
                ,Corto_XmlToArray::ATTRIBUTE_PFX . 'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri'
                , Corto_XmlToArray::ATTRIBUTE_PFX . 'isRequired' => 'true'
            ),

            // UID (example: john.doe)
            array(
                Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:mace:dir:attribute-def:uid'
            ),
            array(
                Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => 'urn:oid:0.9.2342.19200300.100.1.1',
                Corto_XmlToArray::ATTRIBUTE_PFX . 'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
                Corto_XmlToArray::ATTRIBUTE_PFX . 'isRequired' => 'true'
            )
        );

        $entityDescriptor = $this->_server->sign($entityDescriptor);

        $xml = Corto_XmlToArray::array2xml($entityDescriptor);

        $this->_validateXml($xml);

        $this->_server->sendHeader('Content-Type', 'application/xml');
        //$this->_server->sendHeader('Content-Type', 'application/samlmetadata+xml');
        $this->_server->sendOutput($xml);
    }

    /**
     * Loads SSL key for current host
     *
     * @return string pem key
     */
    protected function _loadHostSslKey() 
    {
        $url = $_SERVER['HTTP_HOST'] . ':443';
        $certificate = new EngineBlock_X509Certificate();
        return $certificate->exportPemFromUrl($url);
    }

    /**
     * Adds contact persons (if present) to entity Descriptor
     *
     * @param array $entityDescriptor
     * @param array $entityDetails
     * @return void
     */
    protected function _addContactPersonsToEntityDescriptor(array &$entityDescriptor, array $entityDetails) 
    {
        if(array_key_exists('ContactPersons', $entityDetails)) {
             foreach($entityDetails['ContactPersons'] as $contactPerson) {
                if(empty($contactPerson['EmailAddress'])) {
                    continue;
                }

                $mdContactPerson = array();
                $mdContactPerson[Corto_XmlToArray::ATTRIBUTE_PFX . 'contactType'] = $contactPerson['ContactType'];
                $mdContactPerson['md:EmailAddress'][][Corto_XmlToArray::VALUE_PFX] = $contactPerson['EmailAddress'];

                $entityDescriptor['md:ContactPerson'][] = $mdContactPerson;
            }
        }
    }

    /**
     * Adds DisplayName (if present) to entity Descriptor
     *
     * @param array $entitySSODescriptor
     * @param array $entityDetails
     * @return void
     */
    protected function _addDisplayNamesToEntityDescriptor(array &$entitySSODescriptor, array $entityDetails) 
    {
        if (!isset($entityDetails['DisplayName'])) {
            return;
        }
        foreach($entityDetails['DisplayName'] as $displayLanguageCode => $displayName) {
            if(empty($displayName)) {
                continue;
            }

            $entitySSODescriptor['md:Extensions']['mdui:DisplayName'][] = array(
                Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $displayLanguageCode,
                Corto_XmlToArray::VALUE_PFX => $displayName
            );
        }
    }

    /**
     * Adds ServiceName (if present) to AttributeConsumingService
     *
     * @param array $attributeConsumingService
     * @param array $entityDetails
     * @return void
     */
    protected function _addServiceNamesToAttributeConsumingService(array &$attributeConsumingService, array $entityDetails) 
    {
        foreach($entityDetails['Name'] as $descriptionLanguageCode => $descriptionName) {
            if(empty($descriptionName)) {
                continue;
            }

            $attributeConsumingService['md:ServiceName'][] = array(
                Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $descriptionLanguageCode,
                Corto_XmlToArray::VALUE_PFX => $descriptionName
            );
        }
    }

    /**
     * Adds ServiceDescription (if present) to AttributeConsumingService
     *
     * @param array $attributeConsumingService
     * @param array $entityDetails
     * @return void
     */
    protected function _addServiceDescriptionsToAttributeConsumingService(array &$attributeConsumingService, array $entityDetails) 
    {
        foreach($entityDetails['Description'] as $descriptionLanguageCode => $descriptionName) {
            if(empty($descriptionName)) {
                continue;
            }

            $attributeConsumingService['md:ServiceDescription'][] = array(
                Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $descriptionLanguageCode,
                Corto_XmlToArray::VALUE_PFX => $descriptionName
            );
        }
    }

    /**
     * Validates xml against oasis SAML 2 spec
     *
     * @param string $xml
     * @return void
     * @throws Exception in case validating itself fails or if xml does not validate
     */
    protected function _validateXml($xml)
    {
        $inDebugModus = $this->_server->getConfig('debug', false);
        if($inDebugModus) {
            if(!ini_get('allow_url_fopen')) {
                throw new Exception('Failed validating XML, url_fopen is not allowed');
            }

            // Load schema
            $schemaUrl = 'http://docs.oasis-open.org/security/saml/v2.0/saml-schema-metadata-2.0.xsd';
            $schemaXml = @file_get_contents($schemaUrl);
            if($schemaXml === false) {
                throw new Exception('Failed validating XML, schema url could not be opened: "' . $schemaUrl . '"');
            }

            $schemaXml = $this->_absolutizeSchemaLocations($schemaXml, $schemaUrl);
            
            $dom = new DOMDocument();
            $dom->loadXML($xml);
            if (!@$dom->schemaValidateSource($schemaXml)) {
                 $errorInfo = error_get_last();
                 $errorMessage = $errorInfo['message'];
                 // @todo improve parsing message by creating custom exceptions for which know that structure of messages
                 $parsedErrorMessage = preg_replace('/\{[^}]*\}/', '', $errorMessage);
                echo '<pre>'.htmlentities(Corto_XmlToArray::formatXml($xml)).'</pre>';
                throw new Exception('Metadata XML doesnt validate against XSD at Oasis-open.org: ' . $parsedErrorMessage);
            }
        }
    }

    /**
     * Converts relative schema locations to absolute since php dom validator 
     * does not seem to understand relative links
     *
     * @param   string  $schemaXml
     * @param   string  $schemaUrl
     * @return  string  $absoluteSchemaXml
     */
    protected function _absolutizeSchemaLocations($schemaXml, $schemaUrl) 
    {
        $allSchemaLocationsRegex = '/schemaLocation="(.*)"/';
        preg_match_all($allSchemaLocationsRegex, $schemaXml, $matches);

        $schemaDir = dirname($schemaUrl) . '/';
        $absoluteSchemaXml =$schemaXml;
        foreach($matches[1] as $schemaLocation) {
            $isRelativeLocation = substr($schemaLocation, 0, 4) != 'http';
            if($isRelativeLocation) {
                $absoluteSchemaXml = str_replace('"' . $schemaLocation . '"', '"' . $schemaDir . $schemaLocation . '"', $schemaXml);
            }
        }

        return $absoluteSchemaXml;
    }

    public function artifactResolutionService()
    {
        $postData = Corto_XmlToArray::xml2array(file_get_contents("php://input"));
        $artifact = $postData['SOAP-ENV:Body']['samlp:ArtifactResolve']['saml:Artifact'][Corto_XmlToArray::VALUE_PFX];

        $this->_server->restartSession(sha1($artifact), 'artifact');
        $message = $_SESSION['message'];
        session_destroy();

        $element = $message[Corto_XmlToArray::TAG_NAME_PFX];
        $artifactResponse = array(
            'samlp:ArtifactResponse' => array(
                'xmlns:samlp'   => 'urn:oasis:names:tc:SAML:2.0:protocol',
                'xmlns:saml'    => 'urn:oasis:names:tc:SAML:2.0:assertion',
                'ID'            => $this->_server->getNewId(),
                'Version'       => '2.0',
                'IssueInstant'  => $this->_server->timeStamp(),
                'InResponseTo'  => $postData['SOAP-ENV:Body']['samlp:ArtifactResolve'][Corto_XmlToArray::ATTRIBUTE_PFX . 'ID'],

                'saml:Issuer' => array(Corto_XmlToArray::VALUE_PFX => $this->_server->getCurrentEntityUrl()),
                $element => $message,
            ),
        );
        $this->_server->getBindingsModule()->soapResponse($artifactResponse);
    }

    protected function _showWayf($request, $candidateIdPs)
    {
        // Post to the 'continueToIdp' service
        $action = $this->_server->getCurrentEntityUrl('continueToIdP');

        $requestIssuer = $request['saml:Issuer'][Corto_XmlToArray::VALUE_PFX];

        $remoteEntity = $this->_server->getRemoteEntity($requestIssuer);

        $idpList = $this->_transformIdpsForWAYF($candidateIdPs);

        $output = $this->_server->renderTemplate(
            'discover',
            array(
                'preselectedIdp'    => $this->_server->getCookie('selectedIdp'),
                'action'            => $action,
                'ID'                => $request[Corto_XmlToArray::ATTRIBUTE_PFX . 'ID'],
                'idpList'           => $idpList,
                'metaDataSP'        => $remoteEntity,
            ));
        $this->_server->sendOutput($output);
    }

    protected function _transformIdpsForWayf($idps)
    {
        return $idps;
    }

    protected function _hasStoredConsent($serviceProviderEntityId, $response, $responseAttributes)
    {
        try {
            $dbh = $this->_getConsentDatabaseConnection();
            if (!$dbh) {
                return false;
            }

            $attributesHash = $this->_getAttributesHash($responseAttributes);

            $table = $this->_server->getConfig('ConsentDbTable', 'consent');
            $query = "SELECT * FROM {$table} WHERE hashed_user_id = ? AND service_id = ? AND attribute = ?";
            $parameters = array(
                sha1($this->_getConsentUid($response, $responseAttributes)),
                $serviceProviderEntityId,
                $attributesHash
            );

            $statement = $dbh->prepare($query);
            $statement->execute($parameters);
            $rows = $statement->fetchAll();

            if (count($rows) !== 1) {
                // No stored consent found
                return false;
            }

            // Update usage date
            $statement = $dbh->prepare("UPDATE {$table} SET usage_date = NOW() WHERE attribute = ?");
            $statement->execute(array($attributesHash));

            return true;
        } catch (PDOException $e) {
            throw new Corto_ProxyServer_Exception("Consent retrieval failed! Error: " . $e->getMessage());
        }
    }

    protected function _storeConsent($serviceProviderEntityId, $response, $attributes)
    {
        $dbh = $this->_getConsentDatabaseConnection();
        if (!$dbh) {
            return false;
        }

        $query = "INSERT INTO consent (usage_date, hashed_user_id, service_id, attribute)
                  VALUES (NOW(), ?, ?, ?)
                  ON DUPLICATE KEY UPDATE usage_date=VALUES(usage_date), attribute=VALUES(attribute)";
        $parameters = array(
            sha1($this->_getConsentUid($response, $attributes)),
            $serviceProviderEntityId,
            $this->_getAttributesHash($attributes)
        );

        $statement = $dbh->prepare($query);
        if (!$statement->execute($parameters)) {
            throw new Corto_Module_Services_Exception("Error storing consent: " . var_export($statement->errorInfo(), true));
        }

        return true;
    }

    protected function _getConsentUid($response, $attributes)
    {
        return $attributes['urn:mace:dir:attribute-def:uid'][0];
    }

    /**
     * @return bool|PDO
     */
    protected function _getConsentDatabaseConnection()
    {
        $consentDbDsn = $this->_server->getConfig('ConsentDbDsn', false);
        if (!$consentDbDsn) {
            return false;
        }

        $dbh = new PDO(
            $consentDbDsn,
            $this->_server->getConfig('ConsentDbUser', ''),
            $this->_server->getConfig('ConsentDbPassword', '')
        );
        return $dbh;
    }

    protected function _getAttributesHash($attributes)
    {
        $hashBase = NULL;
        if ($this->_server->getConfig('ConsentStoreValues', true)) {
            ksort($attributes);
            $hashBase = serialize($attributes);
        } else {
            $names = array_keys($attributes);
            sort($names);
            $hashBase = implode('|', $names);
        }
        return sha1($hashBase);
    }
}
