<?php

use \OpenConext\Component\EngineBlockFixtures\IdFrame;
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;

class EngineBlock_Corto_Module_Service_SingleSignOn extends EngineBlock_Corto_Module_Service_Abstract
{
    public function serve($serviceName)
    {
        $log = $this->_server->getSessionLog();

        $response = $this->_displayDebugResponse($serviceName);
        if ($response) {
            return;
        }

        /** @var EngineBlock_Saml2_AuthnRequestAnnotationDecorator|SAML2_AuthnRequest $request */
        $request = $this->_getRequest($serviceName);

        $log->info(sprintf("Fetching service provider matching request issuer '%s'", $request->getIssuer()));
        $sp = $this->_server->getRepository()->fetchServiceProviderByEntityId($request->getIssuer());

        // Flush log if an SP in the requester chain has additional logging enabled
        $log->info("Determining whether service provider in chain requires additional logging");
        $isAdditionalLoggingRequired = EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging(
            EngineBlock_SamlHelper::getSpRequesterChain(
                $sp,
                $request,
                $this->_server->getRepository()
            )
        );

        if ($isAdditionalLoggingRequired) {
            $application = EngineBlock_ApplicationSingleton::getInstance();
            $application->flushLog('Activated additional logging for one or more SPs in the SP requester chain');

            $logger = $application->getLogInstance();
            $logger->info('Raw HTTP request', array('http_request' => (string) $application->getHttpRequest()));
        } else {
            $log->info("No additional logging required");
        }

        // validate custom acs-location (only for unsolicited, normal logins
        //  fall back to default ACS location instead of showing error page)
        if ($serviceName === 'unsolicitedSingleSignOnService') {
            if (!$this->_verifyAcsLocation($request, $sp)) {
                throw new EngineBlock_Corto_Exception_InvalidAcsLocation(
                    'Unsolicited sign-on service called, but unknown or invalid ACS location requested'
                );
            }

            $log->info('Unsolicited sign-on ACS location verified.');
        }

        // The request may specify it ONLY wants a response from specific IdPs
        // or we could have it configured that the SP may only be serviced by specific IdPs
        $scopedIdps = $this->_getScopedIdPs($request);

        $cacheResponseSent = $this->_sendCachedResponse($request, $scopedIdps);
        if ($cacheResponseSent) {
            return;
        }

        // If the scoped proxycount = 0, respond with a ProxyCountExceeded error
        if ($request->getProxyCount() === 0) {
            $log->info("Request does not allow any further proxying, responding with 'ProxyCountExceeded' status");
            $response = $this->_server->createErrorResponse($request, 'ProxyCountExceeded');
            $this->_server->sendResponseToRequestIssuer($request, $response);
            return;
        }

        // Get all registered Single Sign On Services
        // Note that we could also only get the ones that are allowed for this SP, but we may also want to show
        // those that are not allowed.
        $candidateIDPs = $this->_server->getRepository()->findAllIdentityProviderEntityIds();

        $posOfOwnIdp = array_search($this->_server->getUrl('idpMetadataService'), $candidateIDPs);
        if ($posOfOwnIdp !== false) {
            $log->info("Removed ourselves from the candidate IdP list");
            unset($candidateIDPs[$posOfOwnIdp]);
        }


        // If we have scoping, filter out every non-scoped IdP
        if (count($scopedIdps) > 0) {
            $log->info(
                sprintf('%d candidate IdPs before scoping', count($candidateIDPs)),
                array('idps' => array_values($candidateIDPs))
            );

            $candidateIDPs = array_intersect($scopedIdps, $candidateIDPs);

            $log->info(
                sprintf('%d candidate IdPs after scoping', count($candidateIDPs)),
                array('idps' => array_values($candidateIDPs))
            );
        } else {
            $log->info(
                sprintf('No IdP scoping required, %d candidate IdPs', count($candidateIDPs)),
                array('idps' => array_values($candidateIDPs))
            );
        }


        // 0 IdPs found! Throw an exception.
        if (count($candidateIDPs) === 0) {
            throw new EngineBlock_Corto_Module_Service_SingleSignOn_NoIdpsException('No candidate IdPs found');
        }

        // Exactly 1 candidate found, send authentication request to the first one.
        if (count($candidateIDPs) === 1) {
            $idp = array_shift($candidateIDPs);
            $log->info("Only 1 candidate IdP ('$idp'): omitting WAYF, sending authentication request");
            $this->_server->sendAuthenticationRequest($request, $idp);
            return;
        }

        // Multiple IdPs found...

        // > 1 IdPs found, but isPassive attribute given, unable to show WAYF.
        if ($request->getIsPassive()) {
            $log->info('Request is passive, but can be handled by more than one IdP: responding with NoPassive status');
            $response = $this->_server->createErrorResponse($request, 'NoPassive');
            $this->_server->sendResponseToRequestIssuer($request, $response);
            return;
        }

        $authnRequestRepository = new EngineBlock_Saml2_AuthnRequestSessionRepository($log);
        $authnRequestRepository->store($request);

        // Show WAYF
        $log->info("Multiple candidate IdPs: redirecting to WAYF");
        $this->_showWayf($request, $candidateIDPs);
    }

    /**
     * @param string $serviceName
     * @return EngineBlock_Saml2_AuthnRequestAnnotationDecorator
     * @throws EngineBlock_Corto_Module_Bindings_Exception
     * @throws EngineBlock_Corto_Module_Bindings_UnsupportedBindingException
     * @throws EngineBlock_Corto_Module_Bindings_VerificationException
     */
    protected function _getRequest($serviceName)
    {
        $logger = $this->_server->getSessionLog();
        $logger->info('Getting request...');

        if ($serviceName === 'unsolicitedSingleSignOnService') {
            // create unsolicited request object
            $request = $this->_createUnsolicitedRequest();
            $logMessage = 'Created unsolicited SAML request';
        } elseif ($serviceName === 'debugSingleSignOnService') {
            unset($_SESSION['debugIdpResponse']);

            $request = $this->_createDebugRequest();
            $logMessage = 'Created debug SAML request';
        } else {
            // parse SAML request
            $request = $this->_server->getBindingsModule()->receiveRequest();

            // set transparent proxy mode
            if ($this->_server->getConfig('TransparentProxy', false)) {
                $request->setTransparent();
            }

            $logMessage = sprintf(
                "Binding received %s from '%s'",
                $request->wasSigned() ? 'signed SAML request' : 'unsigned SAML request',
                $request->getIssuer()
            );
        }

        // For lack of a better summary, add an equivalent XML representation of the received request to the log message
        $requestXml = $request->toUnsignedXML()->ownerDocument->saveXML();
        $logger->info($logMessage, array('saml_request' => $requestXml));

        return $request;
    }

    /**
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     * @param ServiceProvider $remoteEntity
     * @return bool
     */
    protected function _verifyAcsLocation(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        ServiceProvider $remoteEntity
    ) {
        /** @var SAML2_AuthnRequest $request */
        // show error when acl is given without binding or vice versa
        $acsUrl = $request->getAssertionConsumerServiceURL();
        $acsIndex = $request->getAssertionConsumerServiceIndex();
        $protocolBinding = $request->getProtocolBinding();

        if ($acsUrl XOR $protocolBinding) {
            $this->_server->getSessionLog()->error(
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
     *
     * @return EngineBlock_Saml2_AuthnRequestAnnotationDecorator
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

        return $request;
    }

    /**
     * @return EngineBlock_Saml2_AuthnRequestAnnotationDecorator
     */
    protected function _createDebugRequest()
    {
        $sspRequest = new SAML2_AuthnRequest();
        $sspRequest->setId($this->_server->getNewId(\OpenConext\Component\EngineBlockFixtures\IdFrame::ID_USAGE_SAML2_REQUEST));
        $sspRequest->setIssuer($this->_server->getUrl('spMetadataService'));

        $request = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($sspRequest);
        $request->setDebug();

        return $request;
    }

    protected function _getScopedIdPs(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
    ) {
        $log = $this->_server->getSessionLog();

        /** @var SAML2_AuthnRequest $request */
        $scopedIdPs = $request->getIDPList();
        $presetIdP  = $this->_server->getConfig('Idp');

        if ($presetIdP) {
            // If we have ONE specific IdP pre-configured then we scope to ONLY that Idp
            $log->info(
                'An IdP scope has been configured, choosing it over any IdPs listed in the request',
                array('configured_idp' => $presetIdP, 'request_idps' => $scopedIdPs)
            );

            return array($presetIdP);
        }

        // Add scoped IdPs (allowed IDPs for reply) from request to allowed IdPs for responding
        $log->info('Request lists scoped IdPs', array('request_idps' => $scopedIdPs));

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

        $cachedResponse = $this->_pickCachedResponse($cachedResponses);
        if (!$cachedResponse) {
            return false;
        }

        $this->_server->getSessionLog()->info("Cached response found from Idp");
        // Note that we would like to repurpose the response,
        // but that's tricky as it is probably no longer valid (lifetime is usually something like 5 minutes)
        // so instead we scope the request to that Idp and trust the Idp to do the remembering.
        $this->_server->sendAuthenticationRequest($request, $cachedResponse['idp']);
        return true;
    }

    protected function _pickCachedResponse(array $cachedResponses)
    {
        $idpEntityIds = $this->_server->getRepository()->findAllIdentityProviderEntityIds();

        foreach ($cachedResponses as $cachedResponse) {
            if ($cachedResponse['type'] !== EngineBlock_Corto_Model_Response_Cache::RESPONSE_CACHE_TYPE_IN) {
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
        $identityProviders = $this->_server->getRepository()->findIdentityProvidersByEntityId($idpEntityIds);

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

    private function getNameNl(
        IdentityProvider $identityProvider,
        EngineBlock_Log_Message_AdditionalInfo $additionalLogInfo
    ) {
        if ($identityProvider->displayNameNl) {
            return $identityProvider->displayNameNl;
        }

        if ($identityProvider->nameNl) {
            return $identityProvider->nameNl;
        }

        EngineBlock_ApplicationSingleton::getLog()->warning(
            'No NL displayName and name found for idp: ' . $identityProvider->entityId,
            array('additional_info' => $additionalLogInfo->toArray())
        );

        return $identityProvider->entityId;
    }

    private function getNameEn(
        IdentityProvider $identityProvider,
        EngineBlock_Log_Message_AdditionalInfo $additionalInfo
    ) {
        if ($identityProvider->displayNameEn) {
            return $identityProvider->displayNameEn;
        }

        if ($identityProvider->nameEn) {
            return $identityProvider->nameEn;
        }

        EngineBlock_ApplicationSingleton::getLog()->warning(
            'No EN displayName and name found for idp: ' . $identityProvider->entityId,
            array('additional_info' => $additionalInfo->toArray())
        );

        return $identityProvider->entityId;
    }

    private function getKeywords(IdentityProvider $identityProvider)
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
        if ($serviceName !== 'debugSingleSignOnService') {
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
