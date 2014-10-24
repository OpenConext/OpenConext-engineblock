<?php

use \OpenConext\Component\EngineBlockFixtures\IdFrame;
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProviderEntity;

class EngineBlock_Corto_Module_Service_SingleSignOn extends EngineBlock_Corto_Module_Service_Abstract
{
    const RESPONSE_CACHE_TYPE_IN  = 'in';
    const RESPONSE_CACHE_TYPE_OUT = 'out';

    public function serve($serviceName)
    {
        $response = $this->_displayDebugResponse($serviceName);
        if ($response) {
            return;
        }

        $request = $this->_getRequest($serviceName);

        // Flush log if SP or IdP has additional logging enabled
        $sp = $this->_server->getRepository()->fetchServiceProviderByEntityId($request->getIssuer());

        $isDebugModeEnabled = $this->_server->getConfig('debug', false);
        $isAdditionalLoggingRequired = EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging(
            EngineBlock_SamlHelper::getSpRequesterChain($sp, $request, $this->_server->getRepository())
        );

        if ($isDebugModeEnabled || $isAdditionalLoggingRequired) {
            EngineBlock_ApplicationSingleton::getInstance()->getLogInstance()->flushQueue();
        }

        // validate custom acs-location (only for unsolicited, normal logins
        //  fall back to default ACS location instead of showing error page)
        if ($serviceName === 'unsolicitedSingleSignOnService' && !$this->_verifyAcsLocation($request, $sp)) {
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
        /** @var SAML2_AuthnRequest $request */

        if ($request->getProxyCount() === 0) {
            $this->_server->getSessionLog()->info("SSO: Proxy count exceeded!");
            $response = $this->_server->createErrorResponse($request, 'ProxyCountExceeded');
            $this->_server->sendResponseToRequestIssuer($request, $response);
            return;
        }

        // Get all registered Single Sign On Services
        $candidateIDPs = $this->_server->getRepository()->findAllowedIdpEntityIdsForSp($sp);

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

        // 0 IdPs found! Throw an exception.
        if (count($candidateIDPs) === 0) {
            throw new EngineBlock_Corto_Module_Service_SingleSignOn_NoIdpsException('No Idps found');
        }

        // Exactly 1 candidate found, send authentication request to the first one.
        if (count($candidateIDPs) === 1) {
            $idp = array_shift($candidateIDPs);
            $log->info("SSO: Only 1 candidate IdP: $idp");
            $this->_server->sendAuthenticationRequest($request, $idp);
            return;
        }

        // Multiple IdPs found...

        // > 1 IdPs found, but isPassive attribute given, unable to show WAYF.
        if ($request->getIsPassive()) {
            $log->info("SSO: IsPassive with multiple IdPs!");
            $response = $this->_server->createErrorResponse($request, 'NoPassive');
            $this->_server->sendResponseToRequestIssuer($request, $response);
            return;
        }

        $authnRequestRepository = new EngineBlock_Saml2_AuthnRequestSessionRepository($this->_server->getSessionLog());
        $authnRequestRepository->store($request);

        // Show WAYF
        $this->_server->getSessionLog()->info("SSO: Showing WAYF");
        $this->_showWayf($request, $candidateIDPs);
    }

    protected function _getRequest($serviceName)
    {
        if ($serviceName === 'unsolicitedSingleSignOnService') {
            // create unsolicited request object
            return $this->_createUnsolicitedRequest();
        }

        if ($serviceName === 'debugSingleSignOnService') {
            unset($_SESSION['debugIdpResponse']);
            return $this->_createDebugRequest();
        }

        // parse SAML request
        $request = $this->_server->getBindingsModule()->receiveRequest();

        // set transparent proxy mode
        if ($this->_server->getConfig('TransparentProxy', false)) {
            $request->setTransparent();
        }

        return $request;
    }

    /**
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     * @param array $remoteEntity
     * @return bool
     */
    protected function _verifyAcsLocation(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        array $remoteEntity
    ) {
        /** @var SAML2_AuthnRequest $request */
        // show error when acl is given without binding or vice versa
        $acsUrl = $request->getAssertionConsumerServiceURL();
        $acsIndex = $request->getAssertionConsumerServiceIndex();
        $protocolBinding = $request->getProtocolBinding();

        if ($acsUrl XOR $protocolBinding) {
            $this->_server->getSessionLog()->err(
                "Incomplete ACS location found in request (missing URL or binding)"
            );

            return false;
        }

        // if none specified, all is ok
        if (!$acsUrl && !$acsIndex) {
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
        // Entity ID as requested in GET parameters
        $entityId    = !empty($_GET['sp-entity-id']) ? $_GET['sp-entity-id']: null;

        // Request optional  acs-* parameters
        $acsLocation = !empty($_GET['acs-location']) ? $_GET['acs-location']: null;
        $acsIndex    = !empty($_GET['acs-index'])    ? $_GET['acs-index']   : null;
        $binding     = !empty($_GET['acs-binding'])  ? $_GET['acs-binding'] : null;

        // Requested relay state
        $relayState  = !empty($_GET['RelayState'])   ? $_GET['RelayState']  : null;

        $sspRequest = new SAML2_AuthnRequest();
        $sspRequest->setId($this->_server->getNewId(IdFrame::ID_USAGE_SAML2_REQUEST));
        $sspRequest->setIssuer($entityId);
        $sspRequest->setRelayState($relayState);

        if ($acsLocation) {
            $sspRequest->setAssertionConsumerServiceURL($acsLocation);
            $sspRequest->setProtocolBinding($binding);
        }

        if ($acsIndex) {
            $sspRequest->setAssertionConsumerServiceIndex($acsIndex);
        }

        $request = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($sspRequest);
        $request->setUnsolicited();

        $log = $this->_server->getSessionLog();
        $log->attach($request, 'Unsollicited Request');

        return $request;
    }

    /**
     * Process unsolicited requests
     */
    protected function _createDebugRequest()
    {
        $sspRequest = new SAML2_AuthnRequest();
        $sspRequest->setId($this->_server->getNewId(\OpenConext\Component\EngineBlockFixtures\IdFrame::ID_USAGE_SAML2_REQUEST));
        $sspRequest->setIssuer($this->_server->getUrl('spMetadataService'));

        $request = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($sspRequest);
        $request->setDebug();

        $log = $this->_server->getSessionLog();
        $log->attach($request, 'Debug request');

        return $request;
    }

    protected function _getScopedIdPs(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
    ) {
        $log = $this->_server->getSessionLog();

        /** @var SAML2_AuthnRequest $request */
        $scopedIdPs = $request->getIDPList();
        // Add scoped IdPs (allowed IDPs for reply) from request to allowed IdPs for responding
        if (!empty($scopedIdPs)) {
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

    protected function _sendCachedResponse(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        $scopedIdps
    ) {
        /** @var SAML2_AuthnRequest $request */
        if ($request->getForceAuthn()) {
            return false;
        }

        if (!isset($_SESSION['CachedResponses'])) {
            return false;
        }

        $cachedResponses = $_SESSION['CachedResponses'];

        $requestIssuerEntityId  = $request->getIssuer();

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

        $cachedResponse = $this->_pickCachedResponse($cachedResponses, $requestIssuerEntityId);
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

    protected function _pickCachedResponse(array $cachedResponses, $requestIssuerEntityId)
    {
        // Then we look for OUT responses for this sp
        $idpEntityIds = $this->_server->getRepository()->findAllIdentityProviderEntityIds();
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

            if (isset($cachedResponse['key'])) {
                $this->_server->setKeyId($cachedResponse['key']);
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

            if (isset($cachedResponse['key'])) {
                $this->_server->setKeyId($cachedResponse['key']);
            }

            return $cachedResponse;
        }

        return false;
    }

    protected function _showWayf(EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request, array $candidateIdpEntityIds)
    {
        // Post to the 'continueToIdp' service
        $action = $this->_server->getUrl('continueToIdP');

        $serviceProvider = $this->_server->getRepository()->fetchServiceProviderByEntityId($request->getIssuer());
        $idpList = $this->_transformIdpsForWAYF($candidateIdpEntityIds, $request->isDebugRequest());

        $output = $this->_server->renderTemplate(
            'discover',
            array(
                'preselectedIdp'    => $this->_server->getCookie('selectedIdp'),
                'action'            => $action,
                'ID'                => $request->getId(),
                'idpList'           => $idpList,
                'metaDataSP'        => $serviceProvider,
            ));
        $this->_server->sendOutput($output);
    }

    protected function _transformIdpsForWayf(array $idpEntityIds, $isDebugRequest)
    {
        $identityProviders = $this->_server->getRepository()->fetchIdentityProvidersByEntityId($idpEntityIds);

        $wayfIdps = array();
        foreach ($identityProviders as $identityProvider) {
            if ($identityProvider->entityId === $this->_server->getUrl('idpMetadataService')) {
                // Skip ourselves as a valid Idp
                continue;
            }

            if ($identityProvider->hidden) {
                continue;
            }

            $additionalInfo = EngineBlock_Log_Message_AdditionalInfo::create()->setIdp($identityProvider->entityId);

            $wayfIdp = array(
                'Name_nl'   => $this->getNameNl($identityProvider, $additionalInfo),
                'Name_en'   => $this->getNameEn($identityProvider, $additionalInfo),
                'Logo'      => $identityProvider->logo ? $identityProvider->logo->url : '/media/idp-logo-not-found.png',
                'Keywords'  => $this->getKeywords($identityProvider),
                'Access'    => $identityProvider->enabledInWayf || $isDebugRequest ? '1' : '0',
                'ID'        => md5($identityProvider->entityId),
                'EntityID'  => $identityProvider->entityId,
            );
            $wayfIdps[] = $wayfIdp;
        }

        return $wayfIdps;
    }

    /**
     * @param SAML2_Response|EngineBlock_Saml2_ResponseAnnotationDecorator $response
     */
    protected function _sendDebugMail(EngineBlock_Saml2_ResponseAnnotationDecorator $response)
    {
        $layout = EngineBlock_ApplicationSingleton::getInstance()->getLayout();
        $oldLayout = $layout->getLayout();
        $layout->setLayout('empty');

        $wasEnabled = $layout->isEnabled();
        if ($wasEnabled) {
            $layout->disableLayout();
        }

        $identityProvider = $this->_server->getRepository()->fetchIdentityProviderByEntityId($response->getIssuer());

        $attributes = $response->getAssertion()->getAttributes();
        $output = $this->_server->renderTemplate(
            'debugidpmail',
            array(
                'idp'       => $identityProvider,
                'response'  => $response,
                'attributes'=> $attributes,
            )
        );

        $emailConfiguration = EngineBlock_ApplicationSingleton::getInstance()->getConfigurationValue('email')->idpDebugging;
        $mailer = new Zend_Mail('UTF-8');
        $mailer->setFrom($emailConfiguration->from->address, $emailConfiguration->from->name);
        $mailer->addTo($emailConfiguration->to->address, $emailConfiguration->to->name);
        $mailer->setSubject(sprintf($emailConfiguration->subject, $identityProvider->nameEn));
        $mailer->setBodyText($output);
        $mailer->send();

        $layout->setLayout($oldLayout);
    }

    private function getNameNl(IdentityProviderEntity $identityProvider, $additionalLogInfo)
    {
        if ($identityProvider->displayNameNl) {
            return $identityProvider->displayNameNl;
        }

        if ($identityProvider->nameNl) {
            return $identityProvider->nameNl;
        }

        EngineBlock_ApplicationSingleton::getLog()->warn(
            'No NL displayName and name found for idp: ' . $identityProvider->entityId,
            $additionalLogInfo
        );

        return $identityProvider->entityId;
    }

    private function getNameEn(IdentityProviderEntity $identityProvider, $additionalInfo)
    {
        if ($identityProvider->displayNameEn) {
            return $identityProvider->displayNameEn;
        }

        if ($identityProvider->nameEn) {
            return $identityProvider->nameEn;
        }

        EngineBlock_ApplicationSingleton::getLog()->warn(
            'No EN displayName and name found for idp: ' . $identityProvider->entityId,
            $additionalInfo
        );

        return $identityProvider->entityId;
    }

    private function getKeywords(IdentityProviderEntity $identityProvider)
    {
        if ($identityProvider->keywordsEn) {
            return explode(' ', $identityProvider->keywordsEn);
        }

        if ($identityProvider->keywordsNl) {
            return explode(' ', $identityProvider->keywordsNl);
        }

        return 'Undefined';
    }

    /**
     * @param $serviceName
     * @return bool
     */
    private function _displayDebugResponse($serviceName)
    {
        if ($serviceName === 'debugSingleSignOnService') {
            return false;
        }

        if (isset($_POST['clear'])) {
            unset($_SESSION['debugIdpResponse']);
            return false;
        }

        if (!isset($_SESSION['debugIdpResponse']) || !$_SESSION['debugIdpResponse']) {
            return false;
        }

        /** @var SAML2_Response|EngineBlock_Saml2_ResponseAnnotationDecorator $response */
        $response = $_SESSION['debugIdpResponse'];

        if (isset($_POST['mail'])) {
            $this->_sendDebugMail($response);
        }

        $attributes = $response->getAssertion()->getAttributes();

        $this->_server->sendOutput($this->_server->renderTemplate(
            'debugidpresponse',
            array(
                'idp' => $this->_server->getRepository()->fetchIdentityProviderByEntityId($response->getIssuer()),
                'response' => $response,
                'attributes' => $attributes
            )
        ));
        return true;
    }
}
