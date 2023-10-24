<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use OpenConext\EngineBlock\Logger\Message\AdditionalInfo;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Factory\Factory\ServiceProviderFactory;
use OpenConext\EngineBlock\Metadata\X509\KeyPairFactory;
use OpenConext\EngineBlockBundle\Exception\InvalidArgumentException as EngineBlockBundleInvalidArgumentException;
use SAML2\AuthnRequest;
use SAML2\Response;
use SAML2\XML\saml\Issuer;
use Symfony\Component\HttpFoundation\Request;

class EngineBlock_Corto_Module_Service_SingleSignOn implements EngineBlock_Corto_Module_Service_ServiceInterface
{
    const IS_DEFAULT_IDP_KEY = 'isDefaultIdp';

    /** @var \EngineBlock_Corto_ProxyServer */
    protected $_server;

    /**
     * @var EngineBlock_Corto_XmlToArray
     */
    protected $_xmlConverter;

    /**
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * @var ServiceProviderFactory
     */
    private $_serviceProviderFactory;

    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        EngineBlock_Corto_XmlToArray $xmlConverter,
        Twig_Environment $twig,
        ServiceProviderFactory $serviceProviderFactory
    ) {
        $this->_server = $server;
        $this->_xmlConverter = $xmlConverter;
        $this->twig = $twig;
        $this->_serviceProviderFactory = $serviceProviderFactory;
    }

    public function serve($serviceName, Request $httpRequest)
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();

        $log = $this->_server->getLogger();

        $response = $this->_displayDebugResponse($serviceName);
        if ($response) {
            return;
        }

        $request = $this->_getRequest($serviceName);

        $log->info(sprintf("Fetching service provider matching request issuer '%s'", $request->getIssuer()->getValue()));

        if ($serviceName === 'debugSingleSignOnService') {
            $sp = $this->getEngineSpRole($this->_server);
        } else {
            $sp = $this->_server->getRepository()->fetchServiceProviderByEntityId($request->getIssuer()->getValue());
        }

        // When dealing with an SP that acts as a trusted proxy, we should perform SSO on the proxying SP and not the
        // proxy itself.
        if ($sp->getCoins()->isTrustedProxy()) {
            $proxySp = $sp;
            // Overwrite the trusted proxy SP instance with that of the SP that uses the trusted proxy.
            $sp = $this->_server->findOriginalServiceProvider($request, $log);
        }

        // Exposing entityId to be used when tracking the start of an authentication procedure
        $application->authenticationStateSpEntityId = $sp->entityId;

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
        // or we could have it configured that the SP may only be serviced by
        // specific IdPs.
        //
        // The scope is further limited to the previously used IDP that is
        // allowed for the current issuer, this way the user does not need to
        // go trough the WAYF on subsequent logins.
        $scopedIdps = $this->_limitScopeToRememberedIdp(
            $request,
            $sp,
            $this->_getScopedIdPs($request)
        );

        // If the scoped proxycount = 0, respond with a ProxyCountExceeded error
        if ($request->getProxyCount() === 0) {
            $log->info("Request does not allow any further proxying, responding with 'ProxyCountExceeded' status");
            $response = $this->_server->createProxyCountExceededResponse($request);
            $this->_server->sendResponseToRequestIssuer($request, $response);
            return;
        }

        // Get all registered Single Sign On Services
        // Note that we could also only get the ones that are allowed for this SP, but we may also want to show
        // those that are not allowed.
        $candidateIDPs = $this->_server->getRepository()->findAllIdentityProviderEntityIds($scopedIdps);

        if (count($scopedIdps) > 0) {
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
            // When a trusted proxy is used, the currentServiceProvider is set to the entityId of the original issuing
            // SP. This prevents the display of the Proxy in the feedback information.
            if (isset($proxySp) && $proxySp->getCoins()->isTrustedProxy()) {
                $_SESSION['currentServiceProvider'] = $sp->entityId;
                $_SESSION['proxyServiceProvider'] = $proxySp->entityId;
            }
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

        // Auto-select IdP when 'feature_enable_sso_notification' is enabled and send AuthenticationRequest on success
        if ($application->getDiContainer()->getFeatureConfiguration()->isEnabled("eb.enable_sso_notification")) {
            $idpEntityId = $application->getDiContainer()->getSsoNotificationService()->
                handleSsoNotification($application->getDiContainer()->getSymfonyRequest()->cookies, $this->_server);

            if (!empty($idpEntityId)) {
                try {
                    $log->info("Auto-selecting IdP '$idpEntityId' from SSO notification: " .
                        "omitting WAYF, sending authentication request");
                    $this->_server->sendAuthenticationRequest($request, $idpEntityId);
                    return;
                } catch (EngineBlock_Corto_ProxyServer_Exception $exception) {
                    $log->error("Failed to send authentication request for SSO notification " .
                        "with IdP '$idpEntityId'", array('exception' => $exception));
                }
            }
        }

        // Auto-select IdP when 'wayf.rememberChoice' feature is enabled and is allowed for the current request
        if (($application->getDiContainer()->getRememberChoice() === true) && !($request->getForceAuthn() || $request->isDebugRequest())) {
            $cookies = $application->getDiContainer()->getSymfonyRequest()->cookies->all();
            if (array_key_exists('rememberchoice', $cookies)) {
                $remembered = $cookies['rememberchoice'];
                if (array_search($remembered, $candidateIDPs) !== false) {
                    $log->info("Auto-selecting IdP ('$remembered'): omitting WAYF, sending authentication request");
                    $this->_server->sendAuthenticationRequest($request, $remembered);
                    return;
                }
            }
        }

        // > 1 IdPs found, no WAYF bypass, but isPassive attribute given, unable to show WAYF.
        if ($request->getIsPassive()) {
            $log->info('Request is passive, but can be handled by more than one IdP: responding with NoPassive status');
            $response = $this->_server->createNoPassiveResponse($request);
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
        $logger = $this->_server->getLogger();
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
            // Get the previously parsed request object, see
            // EngineBlock_Corto_Adapter::singleSignOn() and
            // EngineBlock_Corto_Module_Bindings::receiveRequest().
            $request = $this->_server->getReceivedRequest();

            // set transparent proxy mode
            if ($this->_server->getConfig('TransparentProxy', false)) {
                $request->setTransparent();
            }

            $logMessage = sprintf(
                "Binding received %s from '%s'",
                $request->wasSigned() ? 'signed SAML request' : 'unsigned SAML request',
                $request->getIssuer()->getValue()
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
        /** @var AuthnRequest $request */
        // show error when acl is given without binding or vice versa
        $acsUrl = $request->getAssertionConsumerServiceURL();
        $acsIndex = $request->getAssertionConsumerServiceIndex();
        $protocolBinding = $request->getProtocolBinding();

        if ($acsUrl XOR $protocolBinding) {
            $this->_server->getLogger()->error(
                "Incomplete ACS location found in request (missing URL or binding)"
            );

            return false;
        }

        // if none specified, all is ok
        if ($acsUrl === null && $acsIndex === null) {
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
        $destination = null;
        if (array_key_exists('SCRIPT_URL', $_SERVER)){
            $destination = $_SERVER['SCRIPT_URL'];
        } else if (array_key_exists('REDIRECT_URL', $_SERVER)){
            $destination = $_SERVER['REDIRECT_URL'];
        }

        // Requested relay state
        $relayState  = !empty($_GET['RelayState'])   ? $_GET['RelayState']  : null;

        $sspRequest = new AuthnRequest();
        $sspRequest->setId($this->_server->getNewId(EngineBlock_Saml2_IdGenerator::ID_USAGE_SAML2_REQUEST));
        $issuer = new Issuer();
        $issuer->setValue($entityId);
        $sspRequest->setIssuer($issuer);
        $sspRequest->setRelayState($relayState);

        if ($acsLocation) {
            $sspRequest->setAssertionConsumerServiceURL($acsLocation);
            $sspRequest->setProtocolBinding($binding);
        }

        if ($acsIndex) {
            $sspRequest->setAssertionConsumerServiceIndex($acsIndex);
        }

        $request = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($sspRequest);
        if ($keyid = $this->_server->getKeyId()) {
            $request->setKeyId($keyid);
        }
        if ($destination) {
            // Set for logging purposes (LogLogin Command) note that only the REQUEST_URI (no hostname + protocol)
            $sspRequest->setDestination($destination);
        }

        $request->setUnsolicited();

        return $request;
    }

    /**
     * @return EngineBlock_Saml2_AuthnRequestAnnotationDecorator
     */
    protected function _createDebugRequest()
    {
        $sspRequest = new AuthnRequest();
        $sspRequest->setId($this->_server->getNewId(EngineBlock_Saml2_IdGenerator::ID_USAGE_SAML2_REQUEST));
        $issuer = new Issuer();
        $issuer->setValue($this->_server->getUrl('spMetadataService'));
        $sspRequest->setIssuer($issuer);
        $sspRequest->setForceAuthn(true);

        $request = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($sspRequest);
        $request->setDebug();

        return $request;
    }

    protected function _getScopedIdPs(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
    ) {
        $log = $this->_server->getLogger();

        /** @var AuthnRequest $request */
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

        if (count($scopedIdPs) > 0) {
            $log->info('Request lists scoped IdPs', ['request_idps' => $scopedIdPs]);
        }

        return $scopedIdPs;
    }

    /**
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     * @param ServiceProvider $sp
     * @param array $scopedIdps
     * @return array
     */
    protected function _limitScopeToRememberedIdp(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        ServiceProvider $sp,
        array $scopedIdps
    ) {
        /** @var AuthnRequest $request */
        if ($request->getForceAuthn()) {
            return $scopedIdps;
        }

        $rememberedIdp = EngineBlock_Corto_Model_Response_Cache::findRememberedIdp($sp, $scopedIdps);
        if ($rememberedIdp !== null) {
            $this->_server->getLogger()->info("Remembered last used IDP - limiting scope to last selection");

            $scopedIdps = [$rememberedIdp];
        }

        return $scopedIdps;
    }

    protected function _showWayf(EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request, array $candidateIdpEntityIds)
    {
        // Post to the 'continueToIdp' service
        $action = $this->_server->getUrl('continueToIdP');

        $application = EngineBlock_ApplicationSingleton::getInstance();
        $container = $application->getDiContainer();

        $currentLocale = $container->getLocaleProvider()->getLocale();

        $cookies = $container->getSymfonyRequest()->cookies->all();

        if ($request->isDebugRequest()) {
            $serviceProvider = $this->getEngineSpRole($this->_server);
        } else {
            $serviceProvider = $this->_server->findOriginalServiceProvider($request, $application->getLogInstance());
        }

        $idpList = $this->_transformIdpsForWAYF(
            $candidateIdpEntityIds,
            $request->isDebugRequest(),
            $currentLocale,
            $container->getDefaultIdPEntityId()
        );

        $defaultIdPInIdPList = $this->isDefaultIdPPresent($idpList);
        $showDefaultIdpBanner = (bool) $container->shouldDisplayDefaultIdpBannerOnWayf() && $defaultIdPInIdPList;

        $rememberChoiceFeature = $container->getRememberChoice();

        $output = $this->twig->render(
            '@theme/Authentication/View/Proxy/wayf.html.twig',
            [
                'action' => $action,
                'greenHeader' => $serviceProvider->getDisplayName($currentLocale),
                'helpLink' => '/authentication/idp/help-discover?lang=' . $currentLocale,
                'backLink' => $container->isUiOptionReturnToSpActive(),
                'cutoffPointForShowingUnfilteredIdps' => $container->getCutoffPointForShowingUnfilteredIdps(),
                'showIdPBanner' => $showDefaultIdpBanner,
                'rememberChoiceFeature' => $rememberChoiceFeature,
                'showRequestAccess' => $serviceProvider->getCoins()->displayUnconnectedIdpsWayf(),
                'requestId' => $request->getId(),
                'serviceProvider' => $serviceProvider,
                'idpList' => $idpList,
                'cookies' => $cookies,
                'showRequestAccessContainer' => true,
            ]
        );
        $this->_server->sendOutput($output);
    }

    protected function _transformIdpsForWayf(array $idpEntityIds, $isDebugRequest, $currentLocale, $defaultIdpEntityId)
    {
        $identityProviders = $this->_server->getRepository()->findIdentityProvidersByEntityId($idpEntityIds);

        $wayfIdps = array();
        foreach ($identityProviders as $identityProvider) {
            if ($identityProvider->getCoins()->hidden()) {
                continue;
            }

            $isAccessible = $identityProvider->enabledInWayf || $isDebugRequest;
            $isDefaultIdP = false;
            if ($defaultIdpEntityId === $identityProvider->entityId) {
                $isDefaultIdP = true;
            }

            // Do not show the default IdP in the disconnected IdPs section.
            if (!$isAccessible && $isDefaultIdP) {
                continue;
            }

            $additionalInfo = AdditionalInfo::create()->setIdp($identityProvider->entityId);

            $isAccessible = $identityProvider->enabledInWayf || $isDebugRequest;

            $name = $this->getName($currentLocale, $identityProvider, $additionalInfo);

            $wayfIdp = array(
                'Name'   => $name,
                'Logo'      => $identityProvider->getMdui()->hasLogo() ? $identityProvider->getMdui()->getLogo()->url : '/images/placeholder.png',
                'Keywords'  => $this->getKeywords($identityProvider),
                'Access'    => $isAccessible ? '1' : '0',
                'ID'        => md5($identityProvider->entityId),
                'EntityID'  => $identityProvider->entityId,
                self::IS_DEFAULT_IDP_KEY => $isDefaultIdP
            );
            $wayfIdps[] = $wayfIdp;
        }

        return $wayfIdps;
    }

    /**
     * @param Response|EngineBlock_Saml2_ResponseAnnotationDecorator $response
     */
    protected function _sendDebugMail(EngineBlock_Saml2_ResponseAnnotationDecorator $response)
    {
        $identityProvider = $this->_server->getRepository()->fetchIdentityProviderByEntityId($response->getIssuer()->getValue());

        $attributes = $response->getAssertion()->getAttributes();
        $normalizer = new EngineBlock_Attributes_Normalizer($attributes);
        $attributes = $normalizer->normalize();

        $validationResult = EngineBlock_ApplicationSingleton::getInstance()
            ->getDiContainer()
            ->getAttributeValidator()
            ->validate($attributes);

        $output = $this->twig->render(
            '@theme/Authentication/View/Proxy/debug-idp-mail.txt.twig',
            [
                'idp' => $identityProvider,
                'response' => $response,
                'attributes' => $attributes,
                'validationResult' => $validationResult,
            ]
        );

        $diContainer = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
        $emailConfiguration = $diContainer->getEmailIdpDebuggingConfiguration();

        $message = new Swift_Message();
        $message
            ->setSubject(sprintf($emailConfiguration['subject'], $identityProvider->nameEn))
            ->setFrom($emailConfiguration['from']['address'], $emailConfiguration['from']['name'])
            ->setTo($emailConfiguration['to']['address'], $emailConfiguration['to']['name'])
            ->setBody($output, 'text/plain');

        $diContainer->getMailer()->send($message);
    }

    private function getName(string $locale, IdentityProvider $identityProvider, AdditionalInfo $additionalInfo)
    {
        switch ($locale) {
            case "nl":
                return $this->getNameNl($identityProvider, $additionalInfo);
            case "en":
                return $this->getNameEn($identityProvider, $additionalInfo);
            case "pt":
                return $this->getNamePt($identityProvider, $additionalInfo);
            default:
                throw new EngineBlockBundleInvalidArgumentException(
                    sprintf('Trying to get the IdP name for an unsupported language (%s)', $locale)
                );
        }
    }

    private function getNameNl(
        IdentityProvider $identityProvider,
        AdditionalInfo $additionalLogInfo
    ) {
        if ($identityProvider->getMdui()->hasDisplayName('nl')) {
            return $identityProvider->getMdui()->getDisplayName('nl');
        }

        if ($identityProvider->nameNl) {
            return $identityProvider->nameNl;
        }

        EngineBlock_ApplicationSingleton::getLog()->notice(
            'No NL displayName and name found for idp: ' . $identityProvider->entityId,
            array('additional_info' => $additionalLogInfo->toArray())
        );

        return $identityProvider->entityId;
    }

    private function getNameEn(
        IdentityProvider $identityProvider,
        AdditionalInfo $additionalInfo
    ) {
        if ($identityProvider->getMdui()->hasDisplayName('en')) {
            return $identityProvider->getMdui()->getDisplayName('en');
        }

        if ($identityProvider->nameEn) {
            return $identityProvider->nameEn;
        }

        EngineBlock_ApplicationSingleton::getLog()->notice(
            'No EN displayName and name found for idp: ' . $identityProvider->entityId,
            array('additional_info' => $additionalInfo->toArray())
        );

        return $identityProvider->entityId;
    }

    private function getNamePt(
        IdentityProvider $identityProvider,
        AdditionalInfo $additionalInfo
    ) {
        if ($identityProvider->getMdui()->hasDisplayName('pt')) {
            return $identityProvider->getMdui()->getDisplayName('pt');
        }

        if ($identityProvider->namePt) {
            return $identityProvider->namePt;
        }

        EngineBlock_ApplicationSingleton::getLog()->notice(
            'No PT displayName and name found for idp: ' . $identityProvider->entityId,
            array('additional_info' => $additionalInfo->toArray())
        );

        return $identityProvider->entityId;
    }

    private function getKeywords(IdentityProvider $identityProvider)
    {
        if ($identityProvider->getMdui()->hasKeywords('en')) {
            return explode(' ', $identityProvider->getMdui()->getKeywords('en'));
        }

        if ($identityProvider->getMdui()->hasKeywords('nl')) {
            return explode(' ', $identityProvider->getMdui()->getKeywords('nl'));
        }

        if ($identityProvider->getMdui()->hasKeywords('pt')) {
            return explode(' ', $identityProvider->getMdui()->getKeywords('pt'));
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

        $showMailFlashMessage = false;
        if (isset($_POST['mail']) && $_POST['mail'] === 'true') {
            $showMailFlashMessage = true;
        }

        /** @var Response|EngineBlock_Saml2_ResponseAnnotationDecorator $response */
        $response = $_SESSION['debugIdpResponse'];

        $log = $this->_server->getLogger();
        $log->info(
            'Received response to IdP debug page',
            ['saml_response' => $response->toUnsignedXML()->ownerDocument->saveXML()]
        );
        $application = EngineBlock_ApplicationSingleton::getInstance();
        $application->flushLog('IdP debug page; activating all logs');

        if (isset($_POST['mail'])) {
            $this->_sendDebugMail($response);
        }

        $attributes = $response->getAssertion()->getAttributes();
        $normalizer = new EngineBlock_Attributes_Normalizer($attributes);
        $attributes = $normalizer->normalize();

        $validationResult = $application->getDiContainer()
            ->getAttributeValidator()
            ->validate($attributes);

        $this->_server->sendOutput($this->twig->render(
            '@theme/Authentication/View/Proxy/debug-idp-response.html.twig',
            [
                'wide' => true,
                'idp' => $this->_server->getRepository()->fetchIdentityProviderByEntityId($response->getIssuer()->getValue()),
                'attributes' => $attributes,
                'validationResult' => $validationResult,
                'showMailFlashMessage' => $showMailFlashMessage,
            ]
        ));
        return true;
    }

    /**
     * @param EngineBlock_Corto_ProxyServer $proxyServer
     * @return ServiceProvider
     */
    protected function getEngineSpRole(EngineBlock_Corto_ProxyServer $proxyServer)
    {
        $keyId = $proxyServer->getKeyId();
        if (!$keyId) {
            $keyId = KeyPairFactory::DEFAULT_KEY_PAIR_IDENTIFIER;
        }

        $serviceProvider = $this->_serviceProviderFactory->createEngineBlockEntityFrom($keyId);
        return ServiceProvider::fromServiceProviderEntity($serviceProvider);
    }

    private function isDefaultIdPPresent(array $idpList): bool
    {
        foreach ($idpList as $idp) {
            if ($idp[self::IS_DEFAULT_IDP_KEY] === true) {
                return true;
            }
        }
        return false;
    }
}
