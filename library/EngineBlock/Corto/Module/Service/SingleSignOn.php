<?php

class EngineBlock_Corto_Module_Service_SingleSignOn extends EngineBlock_Corto_Module_Service_Abstract
{
    const RESPONSE_CACHE_TYPE_IN  = 'in';
    const RESPONSE_CACHE_TYPE_OUT = 'out';

    public function serve($serviceName)
    {
        $isUnsolicited = ($serviceName === 'unsolicitedSingleSignOnService');

        if ($isUnsolicited) {
            // create unsolicited request object
            $request = $this->_createUnsolicitedRequest();
        }
        else if ($serviceName === 'debugSingleSignOnService') {
            if (isset($_SESSION['debugIdpResponse']) && !isset($_POST['clear'])) {
                $response = $_SESSION['debugIdpResponse'];

                if (isset($_POST['mail'])) {
                    $this->_sendDebugMail($response);
                }

                $this->_server->sendOutput($this->_server->renderTemplate(
                    'debugidpresponse',
                    array(
                        'idp'       => $this->_server->getRemoteEntity($response['saml:Issuer']['__v']),
                        'response'  => $response,
                        'attributes'=> $this->_xmlConverter->attributesToArray(
                            $response['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute']
                        ),
                    )
                ));
                return;
            }
            else {
                unset($_SESSION['debugIdpResponse']);
                $request = $this->_createDebugRequest();
            }
        } else {
            // parse SAML request
            $request = $this->_server->getBindingsModule()->receiveRequest();

            // set transparant proxy mode
            $request[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Transparent'] = $this->_server->getConfig(
                'TransparentProxy',
                false
            );
        }

        // Flush log if SP or IdP has additional logging enabled
        $sp = $this->_server->getRemoteEntity(EngineBlock_SamlHelper::extractIssuerFromMessage($request));
        if (EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging($sp)) {
            EngineBlock_ApplicationSingleton::getInstance()->getLogInstance()->flushQueue();
        }

        // validate custom acs-location (only for unsolicited, normal logins
        //  fall back to default ACS location instead of showing error page)
        if ($isUnsolicited && !$this->_verifyAcsLocation($request, $sp)) {
            throw new EngineBlock_Corto_Exception_InvalidAcsLocation(
                'Unknown or invalid ACS location requested'
            );
        }

        // The request may specify it ONLY wants a response from specific IdPs
        // or we could have it configured that the SP may only be serviced by specific IdPs
        $scopedIdps = $this->_getScopedIdPs($request);

        $cacheResponseSent = $this->_sendCachedResponse($request, $scopedIdps);
        if ($cacheResponseSent) {
            return;
        }

        // If the scoped proxycount = 0, respond with a ProxyCountExceeded error
        if (isset($request['samlp:Scoping'][EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ProxyCount']) && $request['samlp:Scoping'][EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ProxyCount'] == 0) {
            $this->_server->getSessionLog()->info("SSO: Proxy count exceeded!");
            $response = $this->_server->createErrorResponse($request, 'ProxyCountExceeded');
            $this->_server->sendResponseToRequestIssuer($request, $response);
            return;
        }

        // Get all registered Single Sign On Services
        $candidateIDPs = $this->_server->getIdpEntityIds();
        $posOfOwnIdp = array_search($this->_server->getUrl('idpMetadataService'), $candidateIDPs);
        if ($posOfOwnIdp !== false) {
            unset($candidateIDPs[$posOfOwnIdp]);
        }

        $log = $this->_server->getSessionLog();
        $log->attach(array_values($candidateIDPs), 'Candidate IDPs');

        // If we have scoping, filter out every non-scoped IdP
        if (count($scopedIdps) > 0) {
            $candidateIDPs = array_intersect($scopedIdps, $candidateIDPs);
        }

        $log->attach(array_values($candidateIDPs), 'Candidate IDPs (after scoping)');

        // No IdPs found! Send an error response back.
        if (count($candidateIDPs) === 0) {
            $log->info("SSO: No Supported Idps!");
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
            $idp = array_shift($candidateIDPs);
            $log->info("SSO: Only 1 candidate IdP: $idp");
            $this->_server->sendAuthenticationRequest($request, $idp);
            return;
        }
        // Multiple IdPs found...
        else {
            // > 1 IdPs found, but isPassive attribute given, unable to show WAYF
            if (isset($request[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'IsPassive']) && $request[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'IsPassive'] === 'true') {
                $log->info("SSO: IsPassive with multiple IdPs!");
                $response = $this->_server->createErrorResponse($request, 'NoPassive');
                $this->_server->sendResponseToRequestIssuer($request, $response);
                return;
            }
            else {
                // Store the request in the session
                $id = $request[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID'];
                $_SESSION[$id]['SAMLRequest'] = $request;

                // Show WAYF
                $this->_server->getSessionLog()->info("SSO: Showing WAYF");
                $this->_showWayf($request, $candidateIDPs);
                return;
            }
        }
    }

    /**
     * @param array $request
     * @param array $remoteEntity
     * @return bool
     */
    protected function _verifyAcsLocation(array $request, array $remoteEntity)
    {
        // show error when acl is given without binding or vice versa
        if (
            (empty($request['_AssertionConsumerServiceURL']) && !empty($request['_ProtocolBinding'])) ||
            (!empty($request['_AssertionConsumerServiceURL']) && empty($request['_ProtocolBinding']))
        ) {
            $this->_server->getSessionLog()->err(
                "Incomplete ACS location found in request (missing URL or binding)"
            );

            return false;
        }

        // if none specified, all is ok
        if (
            (!$this->_server->hasCustomAssertionConsumer($request)) &&
            (!$this->_server->hasCustomAssertionConsumerIndex($request))
        ) {
            return true;
        }

        $acs = $this->_server->getCustomAssertionConsumer($request, $remoteEntity);

        // acs is only returned on valid and known ACS
        return is_array($acs);
    }

    /**
     * Process unsolicited requests
     */
    protected function _createUnsolicitedRequest()
    {
        // Entity ID as requeted in GET parameters
        $entityId = !empty($_GET['sp-entity-id']) ? $_GET['sp-entity-id'] : null;

        // Request optional  acs-* parameters
        $acsLocation = !empty($_GET['acs-location']) ? $_GET['acs-location'] : null;
        $acsIndex    = !empty($_GET['acs-index']) ? $_GET['acs-index'] : null;
        $binding     = !empty($_GET['acs-binding']) ? $_GET['acs-binding'] : null;

        // Requested relay state
        $relayState = !empty($_GET['RelayState']) ? $_GET['RelayState'] : null;

        // Create 'fake' request object
        $request = array(
            '_ID'         => $this->_server->getNewId(),
            'saml:Issuer' => array(
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $entityId,
            ),
            EngineBlock_Corto_XmlToArray::PRIVATE_PFX => array(
                'Unsolicited' => true,
                'RelayState' => $relayState,
            )
        );

        if ($acsLocation) {
            $request['_AssertionConsumerServiceURL'] = $acsLocation;
            $request['_ProtocolBinding'] = $binding;
        }

        if ($acsIndex) {
            $request['_AssertionConsumerServiceIndex'] = $acsIndex;
        }

        $log = $this->_server->getSessionLog();
        $log->attach($request, 'Unsollicited Request');

        return $request;
    }

    /**
     * Process unsolicited requests
     */
    protected function _createDebugRequest()
    {
        // Create 'fake' request object
        $request = array(
            '_ID'         => $this->_server->getNewId(),
            'saml:Issuer' => array(
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $this->_server->getUrl('spMetadataService'),
            ),
            EngineBlock_Corto_XmlToArray::PRIVATE_PFX => array(
                'Debug' => true,
            )
        );

        $log = $this->_server->getSessionLog();
        $log->attach($request, 'Debug request');

        return $request;
    }

    protected function _getScopedIdPs($request = null)
    {
        $log = $this->_server->getSessionLog();
        $scopedIdPs = array();
        // Add scoped IdPs (allowed IDPs for reply) from request to allowed IdPs for responding
        if (isset($request['samlp:Scoping']['samlp:IDPList']['samlp:IDPEntry'])) {
            foreach ($request['samlp:Scoping']['samlp:IDPList']['samlp:IDPEntry'] as $IDPEntry) {
                $scopedIdPs[] = $IDPEntry[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ProviderID'];
            }

            $log->attach($scopedIdPs, 'Scoped IDPs');
        }

        // If we have ONE specific IdP pre-configured then we scope to ONLY that Idp
        $presetIdP  = $this->_server->getConfig('Idp');
        if ($presetIdP) {
            $scopedIdPs = array($presetIdP);
            $log->attach($scopedIdPs[0], 'Scoped IDP');
        }
        return $scopedIdPs;
    }

    protected function _sendCachedResponse($request, $scopedIdps)
    {
        if (isset($request[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ForceAuthn']) && $request[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ForceAuthn']) {
            return false;
        }

        if (!isset($_SESSION['CachedResponses'])) {
            return false;
        }

        $cachedResponses = $_SESSION['CachedResponses'];

        $requestIssuerEntityId  = $request['saml:Issuer'][EngineBlock_Corto_XmlToArray::VALUE_PFX];

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
            $this->_server->getSessionLog()->info("SSO: Cached response found for SP");
            $response = $this->_server->createEnhancedResponse($request, $cachedResponse['response']);
            $this->_server->sendResponseToRequestIssuer($request, $response);
        }
        else {
            $this->_server->getSessionLog()->info("SSO: Cached response found from Idp");
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

            if (isset($cachedResponse['vo'])) {
                $this->_server->setVirtualOrganisationContext($cachedResponse['vo']);
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

            if (isset($cachedResponse['vo'])) {
                $this->_server->setVirtualOrganisationContext($cachedResponse['vo']);
            }

            return $cachedResponse;
        }

        return false;
    }

    protected function _showWayf($request, $candidateIdPs)
    {
        // Post to the 'continueToIdp' service
        $action = $this->_server->getUrl('continueToIdP');

        $requestIssuer = $request['saml:Issuer'][EngineBlock_Corto_XmlToArray::VALUE_PFX];

        $remoteEntity = $this->_server->getRemoteEntity($requestIssuer);
        $idpList = $this->_transformIdpsForWAYF($candidateIdPs);

        $output = $this->_server->renderTemplate(
            'discover',
            array(
                'preselectedIdp'    => $this->_server->getCookie('selectedIdp'),
                'action'            => $action,
                'ID'                => $request[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID'],
                'idpList'           => $idpList,
                'metaDataSP'        => $remoteEntity,
            ));
        $this->_server->sendOutput($output);
    }

    protected function _transformIdpsForWayf($idps)
    {
        $wayfIdps = array();
        foreach ($idps as $idpEntityId) {
            if ($idpEntityId === $this->_server->getUrl('idpMetadataService')) {
                // Skip ourselves as a valid Idp
                continue;
            }

            $remoteEntities = $this->_server->getRemoteEntities();
            $metadata = ($remoteEntities[$idpEntityId]);
            $additionalInfo = EngineBlock_Log_Message_AdditionalInfo::create()->setIdp($idpEntityId);

            if (isset($metadata['DisplayName']['nl'])) {
                $nameNl = $metadata['DisplayName']['nl'];
            }
            else if (isset($metadata['Name']['nl'])) {
                $nameNl = $metadata['Name']['nl'];
            }
            else {
                $nameNl = 'Geen naam gevonden';
                EngineBlock_ApplicationSingleton::getLog()->warn('No NL displayName and name found for idp: ' . $idpEntityId, $additionalInfo);
            }

            if (isset($metadata['DisplayName']['en'])) {
                $nameEn = $metadata['DisplayName']['en'];
            }
            else if (isset($metadata['Name']['en'])) {
                $nameEn = $metadata['Name']['en'];
            }
            else {
                $nameEn = 'No name found';
                EngineBlock_ApplicationSingleton::getLog()->warn('No EN displayName and name found for idp: ' . $idpEntityId, $additionalInfo);
            }

            $wayfIdp = array(
                'Name_nl' => $nameNl,
                'Name_en' => $nameEn,
                'Logo' => isset($metadata['Logo']['URL']) ? $metadata['Logo']['URL']
                    : EngineBlock_View::staticUrl() . '/media/idp-logo-not-found.png',
                'Keywords' => isset($metadata['Keywords']['en']) ? explode(' ', $metadata ['Keywords']['en'])
                    : isset($metadata['Keywords']['nl']) ? explode(' ', $metadata['Keywords']['nl']) : 'Undefined',
                'Access' => '1',
                'ID' => md5($idpEntityId),
                'EntityId' => $idpEntityId,
            );
            $wayfIdps[] = $wayfIdp;
        }

        return $wayfIdps;
    }

    protected function _sendDebugMail($response)
    {
        $layout = $this->_server->layout();
        $oldLayout = $layout->getLayout();
        $layout->setLayout('empty');

        {
            $wasEnabled = $layout->isEnabled();
            if ($wasEnabled) {
                $layout->disableLayout();
            }

            $idp = $this->_server->getRemoteEntity($response['saml:Issuer']['__v']);
            $attributes = $this->_xmlConverter->attributesToArray(
                $response['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute']
            );
            $output = $this->_server->renderTemplate('debugidpmail', array(
                    'idp'       => $idp,
                    'response'  => $response,
                    'attributes'=> $attributes,
            ));

            $emailConfiguration = EngineBlock_ApplicationSingleton::getInstance()->getConfigurationValue('email')->idpDebugging;
            $mailer = new Zend_Mail('UTF-8');
            $mailer->setFrom($emailConfiguration->from->address, $emailConfiguration->from->name);
            $mailer->addTo($emailConfiguration->to->address, $emailConfiguration->to->name);
            $mailer->setSubject(sprintf($emailConfiguration->subject, $idp['Name']['en']));
            $mailer->setBodyText($output);
            $mailer->send();
        }
        $layout->setLayout($oldLayout);
    }
}
