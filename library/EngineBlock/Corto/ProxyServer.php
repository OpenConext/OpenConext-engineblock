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

use OpenConext\EngineBlock\Exception\MissingParameterException;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Loa;
use OpenConext\EngineBlock\Metadata\MetadataRepository\EntityNotFoundException;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlock\Metadata\MfaEntity;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\TransparentMfaEntity;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationState;
use OpenConext\EngineBlockBundle\Exception\UnknownKeyIdException;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use Psr\Log\LoggerInterface;
use SAML2\Assertion;
use SAML2\AuthnRequest;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Response;
use SAML2\XML\saml\Issuer;
use SAML2\XML\saml\NameID;
use SAML2\XML\saml\SubjectConfirmation;
use SAML2\XML\saml\SubjectConfirmationData;

class EngineBlock_Corto_ProxyServer
{
    const ID_PREFIX = 'CORTO';

    const MODULE_BINDINGS = 'Bindings';
    const MODULE_SERVICES = 'Services';

    const TEMPLATE_SOURCE_FILESYSTEM = 'filesystem';
    const TEMPLATE_SOURCE_MEMORY = 'memory';

    const MESSAGE_TYPE_REQUEST = 'SAMLRequest';
    const MESSAGE_TYPE_RESPONSE = 'SAMLResponse';

    // Todo: Make this mapping obsolete by rewriting the `getParametersFromUrl` method
    protected $_serviceToControllerMapping = array(
        'singleSignOnService'               => '/authentication/idp/single-sign-on',
        'debugSingleSignOnService'          => '/authentication/sp/debug',
        'continueToIdP'                     => '/authentication/idp/process-wayf',

        'assertionConsumerService'          => '/authentication/sp/consume-assertion',
        'stepupAssertionConsumerService'    => '/authentication/stepup/consume-assertion',
        'continueToSP'                      => '/authentication/sp/process-consent',
        'provideConsentService'             => '/authentication/idp/provide-consent',
        'processConsentService'             => '/authentication/idp/process-consent',
        'processedAssertionConsumerService' => '/authentication/proxy/processed-assertion',

        'idpMetadataService'                => '/authentication/idp/metadata',
        'spMetadataService'                 => '/authentication/sp/metadata',
        'stepupMetadataService'             => '/authentication/stepup/metadata',
        'singleLogoutService'               => '/logout'
    );

    // Todo: Make this mapping obsolete by updating all proxyserver getUrl callers. If they would reference the correct
    // route name in the first place, this mapping can be removed
    protected $_serviceToRouteNameMapping = array(
        'singleSignOnService'               => 'authentication_idp_sso',
        'debugSingleSignOnService'          => 'authentication_sp_debug',
        'continueToIdP'                     => 'authentication_wayf_process_wayf',

        'assertionConsumerService'          => 'authentication_sp_consume_assertion',
        'stepupAssertionConsumerService'    => 'authentication_stepup_consume_assertion',
        'continueToSP'                      => 'authentication_sp_process_consent',
        'processConsentService'             => 'authentication_idp_process_consent',
        'processedAssertionConsumerService' => 'authentication_proxy_processed_assertion',

        'idpMetadataService'                => 'metadata_idp',
        'spMetadataService'                 => 'metadata_sp',
        'stepupMetadataService'             => 'metadata_stepup',
        'singleLogoutService'               => 'authentication_logout'
    );

    protected $_servicesNotNeedingSession = array(
        'debugSingleSignOnService',
        'idpCertificateService',
        'idpMetadataService',
        'idpsMetadataService',
        'singleLogoutService',
        'singleSignOnService',
        'spMetadataService',
        'stepupMetadataService',
        'unsolicitedSingleSignOnService',
    );

    protected $_headers = array();
    protected $_output;

    protected $_keyId = null;

    protected $_server;

    /** @var Psr\Log\LoggerInterface */
    protected $_logger;

    protected $_configs;

    protected $_defaultCertificates = null;
    protected $_keyPairs = array();

    protected $_modules = array();
    protected $_templateSource;
    protected $_processingMode = false;

    /**
     * @var EngineBlock_Saml2_AuthnRequestAnnotationDecorator
     */
    private $receivedRequest;

    /**
     * @var MetadataRepositoryInterface
     */
    private $_repository;

    /**
     * @var Twig_Environment
     */
    private $twig;

    public function __construct(Twig_Environment $twig)
    {
        $this->_server = $this;
        $this->twig = $twig;
    }

//////// GETTERS / SETTERS /////////

    public function setKeyId($keyId)
    {
        $this->_keyId = $keyId;
    }

    public function getKeyId()
    {
        return $this->_keyId;
    }

    public function getOutput()
    {
        return $this->_output;
    }

    public function getHeaders()
    {
        return $this->_headers;
    }

    public function sendOutput($rawOutput)
    {
        $this->_output = $rawOutput;
    }

    public function sendHeader($name, $value)
    {
        $this->_headers[$name] = $value;
    }

    public function setProcessingMode()
    {
        $this->_processingMode = true;
        return $this;
    }

    public function unsetProcessingMode()
    {
        $this->_processingMode = false;
        return $this;
    }

    public function isInProcessingMode()
    {
        return $this->_processingMode;
    }

    /**
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     * @return EngineBlock_Corto_ProxyServer
     */
    public function setReceivedRequest(EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request)
    {
        $this->receivedRequest = $request;

        return $this;
    }

    /**
     * @return EngineBlock_Saml2_AuthnRequestAnnotationDecorator
     */
    public function getReceivedRequest()
    {
        return $this->receivedRequest;
    }

    /**
     * @return EngineBlock_Corto_Module_Bindings
     */
    public function getBindingsModule()
    {
        return $this->_getModule(self::MODULE_BINDINGS);
    }

    /**
     * @param EngineBlock_Corto_Module_Bindings $bindingsInstance
     * @return EngineBlock_Corto_ProxyServer
     */
    public function setBindingsModule(EngineBlock_Corto_Module_Bindings $bindingsInstance)
    {
        return $this->_setModule(self::MODULE_BINDINGS, $bindingsInstance);
    }

    /**
     * @return EngineBlock_Corto_Module_Services
     */
    public function getServicesModule()
    {
        return $this->_getModule(self::MODULE_SERVICES);
    }

    /**
     * @param EngineBlock_Corto_Module_Services $servicesInstance
     * @return EngineBlock_Corto_ProxyServer
     */
    public function setServicesModule(EngineBlock_Corto_Module_Services $servicesInstance)
    {
        return $this->_setModule(self::MODULE_SERVICES, $servicesInstance);
    }

    /**
     * @param string $name
     * @return EngineBlock_Corto_Module_Abstract
     */
    protected function _getModule($name)
    {
        return $this->_modules[$name];
    }

    /**
     * @param  $name
     * @param  $moduleInstance
     * @return EngineBlock_Corto_ProxyServer
     */
    protected function _setModule($name, EngineBlock_Corto_Module_Abstract $moduleInstance)
    {
        $this->_modules[$name] = $moduleInstance;
        return $this;
    }

    public function getConfig($name, $default = null)
    {
        if (isset($this->_configs[$name])) {
            return $this->_configs[$name];
        }
        return $default;
    }

    public function getConfigs()
    {
        return $this->_configs;
    }

    public function setConfig($name, $value)
    {
        $this->_configs[$name] = $value;
        return $this;
    }

    public function setConfigs($configs)
    {
        $this->_configs = $configs;
        return $this;
    }

    /**
     * @param EngineBlock_X509_KeyPair[] $keyPairs
     */
    public function setKeyPairs(array $keyPairs = array())
    {
        $this->_keyPairs = $keyPairs;
    }

    /**
     * @param bool $forceDefaultKey Always return the default keypair
     * @return EngineBlock_X509_KeyPair
     * @throws EngineBlock_Corto_ProxyServer_Exception
     * @throws UnknownKeyIdException
     */
    public function getSigningCertificates(bool $forceDefaultKey = false)
    {
        $keyId = 'default';
        if (!$forceDefaultKey && $this->_keyId) {
            $keyId = $this->_keyId;
        }

        if (!isset($this->_keyPairs[$keyId])) {
            throw new UnknownKeyIdException($keyId);
        }
        return $this->_keyPairs[$keyId];
    }

    public function getUrl($serviceName = "", $remoteEntityId = "")
    {
        $urlProvider = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getUrlProvider();

        if (!isset($this->_serviceToRouteNameMapping[$serviceName])) {
            throw new EngineBlock_Corto_ProxyServer_Exception(
                sprintf('Unable to map service "%s" to a Symfony route.', $serviceName)
            );
        }
        $routeName = $this->_serviceToRouteNameMapping[$serviceName];

        return $urlProvider->getUrl($routeName, $this->_processingMode, $this->_keyId, $remoteEntityId);
    }

    /**
     * @param MetadataRepositoryInterface $repository
     * @return $this
     */
    public function setRepository(MetadataRepositoryInterface $repository)
    {
        $this->_repository = $repository;
        return $this;
    }

    /**
     * @return MetadataRepositoryInterface
     */
    public function getRepository()
    {
        return $this->_repository;
    }

//////// MAIN /////////

    public function serve($serviceName, $remoteIdpMd5 = "")
    {
        $logger = $this->getLogger();

        if (!isset($_COOKIE[session_name()]) && !in_array($serviceName, $this->_servicesNotNeedingSession)) {
            $application = EngineBlock_ApplicationSingleton::getInstance();
            $logger->info('Session not started', array('http_request' => (string) $application->getHttpRequest()));
            throw new EngineBlock_Corto_Module_Services_SessionNotStartedException('Session not started');
        }

        if (!empty($remoteIdpMd5)) {
            $this->setRemoteIdpMd5($remoteIdpMd5);
        }

        if (empty($remoteIdpMd5)) {
            $logger->info("Calling service '$serviceName'");
        } else {
            $logger->info("Calling service '$serviceName' for specific remote IdP '$remoteIdpMd5'");
        }

        $this->getServicesModule()->serve($serviceName);

        $logger->info("Done calling service '$serviceName'");
    }

    public function setRemoteIdpMd5($remoteIdPMd5)
    {
        $entityId = $this->_repository->findIdentityProviderEntityIdByMd5Hash($remoteIdPMd5);

        if ($entityId !== null) {
            $this->_configs['Idp'] = $entityId;
            $this->_configs['TransparentProxy'] = true;
            $this->getLogger()->info(
                "Detected pre-selection of $entityId as IdP, switching to transparent mode"
            );
        } else {
            $this->getLogger()->notice(sprintf('Unable to map remote IdpMD5 "%s" to a remote entity.', $remoteIdPMd5));

            throw new EngineBlock_Corto_Exception_UnknownPreselectedIdp(
                sprintf('Unable to map remote IdpMD5 "%s" to a remote entity!', $remoteIdPMd5),
                $remoteIdPMd5
            );
        }

        return $this;
    }

////////  REQUEST HANDLING /////////

    public function sendAuthenticationRequest(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $spRequest,
        $idpEntityId
    ) {
        $identityProvider = $this->getRepository()->fetchIdentityProviderByEntityId($idpEntityId);

        $ebRequest = EngineBlock_Saml2_AuthnRequestFactory::createFromRequest($spRequest, $identityProvider, $this);

        // Store the authentication state based on the request id and the sp entity id.
        $spEntityId = $spRequest->getSspMessage()->getIssuer()->getValue();
        $serviceProvider = new Entity(new EntityId($spEntityId), EntityType::SP());

        $sspMessage = $ebRequest->getSspMessage();
        if (!$sspMessage instanceof AuthnRequest) {
            throw new EngineBlock_Corto_ProxyServer_Exception(sprintf('Unknown message type: "%s"', get_class($sspMessage)));
        }

        try {
            $originalSpEnitytId = $this->findOriginalServiceProvider($spRequest, $this->_logger)->entityId;
        } catch (EntityNotFoundException $e) {
            // On debug requests, the entity ID can not be found in the database as this is the EngineBlock internal
            // entity which does not reside in the database.
            $originalSpEnitytId = $spEntityId;
        }

        // Add authncontextclassref if configured
        $service = $identityProvider->getCoins()->mfaEntities()->findByEntityId($originalSpEnitytId);
        if ($service instanceof MfaEntity) {
            $sspMessage->setRequestedAuthnContext([
                'AuthnContextClassRef' => [
                    $service->level(),
                ],
            ]);
        } elseif ($service instanceof TransparentMfaEntity) {
            // When transparent_authn_context is configured, forward the SP AuthnClassRefContext to the IdP
            // See: https://www.pivotaltracker.com/story/show/173305494
            $sspMessage->setRequestedAuthnContext($spRequest->getRequestedAuthnContext());
        } elseif ($spRequest->getRequestedAuthnContext() === null) {
            // The request didn't have a RequestedAuthnContext set, but we do have a default to fall back to
            $defaultRAC = $identityProvider->getCoins()->defaultRAC();
            if ($defaultRAC !== null) {
                $sspMessage->setRequestedAuthnContext([
                    'AuthnContextClassRef' => [
                        $defaultRAC,
                    ],
                ]);
            }
        }

        $authenticationState = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()
            ->getAuthenticationStateHelper()
            ->getAuthenticationState();
        $authenticationState->startAuthenticationOnBehalfOf($ebRequest->getId(), $serviceProvider);

        // Store the original Request
        $authnRequestRepository = new EngineBlock_Saml2_AuthnRequestSessionRepository($this->_logger);
        $authnRequestRepository->store($spRequest);
        $authnRequestRepository->link($ebRequest, $spRequest);


        $this->getBindingsModule()->send($ebRequest, $identityProvider);
    }

    public function sendStepupAuthenticationRequest(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $spRequest,
        IdentityProvider $identityProvider,
        Loa $authnContextClassRef,
        NameID $nameId,
        bool $isForceAuthn
    ) {
        $ebRequest = EngineBlock_Saml2_AuthnRequestFactory::createFromRequest(
            $spRequest,
            $identityProvider,
            $this,
            'stepupMetadataService',
            'stepupAssertionConsumerService'
        );

        $ebRequest->setForceAuthn($isForceAuthn);

        $sspMessage = $ebRequest->getSspMessage();
        if (!$sspMessage instanceof AuthnRequest) {
            throw new EngineBlock_Corto_ProxyServer_Exception(sprintf('Unknown message type: "%s"', get_class($sspMessage)));
        }

        // Add Stepup specific data
        $sspMessage->setRequestedAuthnContext([
            'AuthnContextClassRef' => [
                $authnContextClassRef->getIdentifier()
            ],
            'Comparison' => 'minimal',
        ]);
        $nameIdOverwrite = new NameID();
        $nameIdOverwrite->setValue($nameId->getValue());
        $nameIdOverwrite->setFormat(Constants::NAMEID_UNSPECIFIED);
        $sspMessage->setNameId($nameIdOverwrite);

        // See: UPGRADING.md -> ## 6.13 -> 6.14
        $container = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
        $entityIdOverrideValue = $container->getStepupEntityIdOverrideValue();
        $features = $container->getFeatureConfiguration();
        $isConfigured = $features->hasFeature('eb.stepup.sfo.override_engine_entityid');
        $isEnabled = $features->isEnabled('eb.stepup.sfo.override_engine_entityid');

        if ($isEnabled && $isConfigured) {
            if (empty($entityIdOverrideValue)) {
                throw new MissingParameterException(
                    'When feature "feature_stepup_sfo_override_engine_entityid" is enabled,
                     you must provide the "stepup.sfo.override_engine_entityid" parameter.'
                );
            }
            $this->_logger->notice(
                sprintf(
                    'Feature eb.stepup.sfo.override_engine_entityid is enabled, overriding the Issuer of the AR to the ' .
                    'StepUp Gateway. Updated the Issuer to "%s"',
                    $entityIdOverrideValue
                )
            );
            $issuer = new Issuer();
            $issuer->setValue($entityIdOverrideValue);
            $sspMessage->setIssuer($issuer);
        }

        // Link with the original Request
        $authnRequestRepository = new EngineBlock_Saml2_AuthnRequestSessionRepository($this->_logger);
        $authnRequestRepository->store($spRequest);
        $authnRequestRepository->link($ebRequest, $spRequest);

        $this->getBindingsModule()->send($ebRequest, $identityProvider, true);
    }

    function sendConsentAuthenticationRequest(
        EngineBlock_Saml2_ResponseAnnotationDecorator $receivedResponse,
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $receivedRequest,
        AbstractRole $serviceProvider,
        AuthenticationState $authenticationState
    ) {
        $this->setProcessingMode();
        $newResponse = $this->_server->createEnhancedResponse($receivedRequest, $receivedResponse);

        // Change the destiny of the received response
        $inResponseTo = $receivedResponse->getInResponseTo();
        $newResponse->setInResponseTo($inResponseTo);
        $newResponse->setDestination('/authentication/idp/provide-consent');
        $newResponse->setDeliverByBinding('INTERNAL');
        $newResponse->setReturn($this->_server->getUrl('processedAssertionConsumerService'));

        $idp = $this->_server->getRepository()->fetchIdentityProviderByEntityId($receivedResponse->getIssuer()->getValue());
        $identityProvider = new Entity(new EntityId($idp->entityId), EntityType::IdP());
        $authenticationState->authenticatedAt($inResponseTo, $identityProvider);

        $this->_server->getBindingsModule()->send($newResponse, $serviceProvider);
    }

//////// RESPONSE HANDLING ////////

    public function createProxyCountExceededResponse(EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request)
    {
        $response = $this->_createBaseResponse($request);
        $response->setStatus([
                'Code' => Constants::STATUS_RESPONDER,
                'SubCode' => Constants::STATUS_PROXY_COUNT_EXCEEDED,
        ]);

        return $response;
    }

    public function createNoPassiveResponse(EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request)
    {
        $response = $this->_createBaseResponse($request);
        $response->setStatus([
                'Code' => Constants::STATUS_RESPONDER,
                'SubCode' => Constants::STATUS_NO_PASSIVE,
        ]);

        return $response;
    }

    public function createTransparentErrorResponse(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        EngineBlock_Saml2_ResponseAnnotationDecorator $receivedResponse
    ) {
        $response = $this->_createBaseResponse($request);
        $this->_logger->info('Setting the status of the original (error) response on the SAML Response for the SP');
        $response->setStatus($receivedResponse->getStatus());
        return $response;
    }

    /**
     * @param AuthnRequest|EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     * @param Response|EngineBlock_Saml2_ResponseAnnotationDecorator $sourceResponse
     *
     * @return EngineBlock_Saml2_ResponseAnnotationDecorator|Response
     */
    public function createEnhancedResponse(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        EngineBlock_Saml2_ResponseAnnotationDecorator $sourceResponse
    ) {
        $newResponse = $this->_createBaseResponse($request);

        // We don't support multiple assertions, only use the first one.
        $sourceAssertions = $sourceResponse->getAssertions();
        $sourceAssertion = $sourceAssertions[0];

        // Store the Origin response and issuer (from the IdP)
        $newResponse->setOriginalResponse(
            $sourceResponse->getOriginalResponse() ?
                $sourceResponse->getOriginalResponse() :
                $sourceResponse
        );
        $newResponse->setOriginalIssuer(
            $sourceResponse->getOriginalIssuer() ?
                $sourceResponse->getOriginalIssuer() :
                $newResponse->getOriginalResponse()->getIssuer()->getValue()
        );

        // Copy over the Status (which should be success)
        $newResponse->setStatus($sourceResponse->getStatus());

        // Create a new assertion by us.
        $newAssertion = new Assertion();
        $newResponse->setAssertions(array($newAssertion));
        $newAssertion->setId($this->getNewId(EngineBlock_Saml2_IdGenerator::ID_USAGE_SAML2_ASSERTION));
        $newAssertion->setIssueInstant(time());
        $newAssertion->setIssuer($newResponse->getIssuer());

        // Unless of course we are in 'stealth' / transparent mode, in which case,
        // pretend to be the Identity Provider.
        $serviceProvider = $this->getRepository()->fetchServiceProviderByEntityId($request->getIssuer()->getValue());
        $mustProxyTransparently = ($request->isTransparent() || $serviceProvider->getCoins()->isTransparentIssuer());
        if (!$this->isInProcessingMode() && $mustProxyTransparently) {
            $issuer = new Issuer();
            $issuer->setValue($newResponse->getOriginalIssuer());
            $newResponse->setIssuer($issuer);
            $newAssertion->setIssuer($issuer);
        }

        // Copy over the NameID for now...
        // (further on in the filters we'll have more info and set this to something better)
        $sourceNameId = $sourceAssertion->getNameId();
        if ($sourceNameId->getValue() && $sourceNameId->getFormat()) {
            $newAssertion->setNameId($sourceNameId);
        }

        // Set up the Subject Confirmation element.
        $subjectConfirmation = new SubjectConfirmation();
        $subjectConfirmation->setMethod(Constants::CM_BEARER);
        $newAssertion->setSubjectConfirmation(array($subjectConfirmation));
        $subjectConfirmationData = new SubjectConfirmationData();
        $subjectConfirmation->setSubjectConfirmationData($subjectConfirmationData);

        // Confirm where we are sending it.
        $acs = $this->getRequestAssertionConsumer($request);
        $subjectConfirmationData->setRecipient($acs->location);

        // Confirm that this is in response to their AuthnRequest (unless, you know, it isn't).
        if (!$request->isUnsolicited()) {
            /** @var AuthnRequest $request */
            $subjectConfirmationData->setInResponseTo($request->getId());
        }

        // Note that it is valid for some 5 minutes.
        $notOnOrAfter = time() + $this->getConfig('NotOnOrAfter', 300);
        $newAssertion->setNotBefore(time() - 1);
        if ($sourceAssertion->getSessionNotOnOrAfter()) {
            $newAssertion->setSessionNotOnOrAfter($sourceAssertion->getSessionNotOnOrAfter());
        }
        $newAssertion->setNotOnOrAfter($notOnOrAfter);
        $subjectConfirmationData->setNotOnOrAfter($notOnOrAfter);

        // And only valid for the SP that requested it.
        $newAssertion->setValidAudiences(array($request->getIssuer()->getValue()));

        // Copy over the Authentication information because the IdP did the authentication, not us.
        $newAssertion->setAuthnInstant($sourceAssertion->getAuthnInstant());
        $newAssertion->setSessionIndex($newAssertion->getId());

        $newAssertion->setAuthnContextClassRef($sourceAssertion->getAuthnContextClassRef());
        $newAssertion->setAuthnContextDeclRef($sourceAssertion->getAuthnContextDeclRef());
        if ($sourceAssertion->getAuthnContextDecl()) {
            $newAssertion->setAuthnContextDecl($sourceAssertion->getAuthnContextDecl());
        }

        // Copy over the Authenticating Authorities and add the EntityId of the Source Response Issuer.
        // Note that because EB generates multiple responses, this will likely result in:
        // "https://engine/../idp/metadata" !== "https://original-idp/../idpmetadata" => true, gets added
        // "https://engine/../idp/metadata" !== "https://engine/../idp/metadata" => false, does not get added
        // "https://engine/../idp/metadata" !== "https://engine/../idp/metadata" => false, does not get added

        $authenticatingAuthorities = $sourceAssertion->getAuthenticatingAuthority();
        if ($this->getUrl('idpMetadataService') !== $sourceResponse->getIssuer()->getValue()) {
            $authenticatingAuthorities[] = $sourceResponse->getIssuer()->getValue();
        }
        $newAssertion->setAuthenticatingAuthority($authenticatingAuthorities);

        // Copy over the attributes
        $newAssertion->setAttributes($sourceAssertion->getAttributes());
        $newAssertion->setAttributeNameFormat(Constants::NAMEFORMAT_URI);

        return $newResponse;
    }

    /**
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     */
    protected function _createBaseResponse(EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request)
    {
        if ($keyId = $request->getKeyId()) {
            $this->setKeyId($keyId);
        }
        $requestWasUnsolicited = $request->isUnsolicited();

        $response = new Response();
        /** @var AuthnRequest $request */
        $response->setRelayState($request->getRelayState());
        $response->setId($this->getNewId(EngineBlock_Saml2_IdGenerator::ID_USAGE_SAML2_RESPONSE));
        $response->setIssueInstant(time());
        if (!$requestWasUnsolicited) {
            $response->setInResponseTo($request->getId());
        }
        $response->setDestination($request->getIssuer()->getValue());
        $issuer = new Issuer();
        $issuer->setValue($this->getUrl('idpMetadataService', $request->getIssuer()->getValue()));
        $response->setIssuer($issuer);

        $acs = $this->getRequestAssertionConsumer($request);
        $response->setDestination($acs->location);
        $response->setStatus(array('Code' => Constants::STATUS_SUCCESS));

        $response = new EngineBlock_Saml2_ResponseAnnotationDecorator($response);
        $response->setDeliverByBinding($acs->binding);
        return $response;
    }

    /**
     * Returns the a custom ACS location when provided in the request
     * or the default ACS location when omitted.
     *
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     * @return Service
     */
    public function getRequestAssertionConsumer(EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request)
    {
        /** @var AuthnRequest $request */
        $serviceProvider = $this->getRepository()->fetchServiceProviderByEntityId($request->getIssuer()->getValue());

        // parse and validate custom ACS location
        $custom = $this->getCustomAssertionConsumer($request, $serviceProvider);
        if ($custom) {
            return $custom;
        }

        // return default ACS or fail
        return $this->getDefaultAssertionConsumer($serviceProvider);
    }

    /**
     * Returns the default ACS location for given entity
     *
     * @param ServiceProvider $serviceProvider
     * @return Service
     * @throws EngineBlock_Corto_ProxyServer_Exception
     */
    public function getDefaultAssertionConsumer(ServiceProvider $serviceProvider)
    {
        // find first ACS URL that has a binding supported by EB
        foreach ($serviceProvider->assertionConsumerServices as $acs) {
            if ($this->getBindingsModule()->isSupportedBinding($acs->binding)) {
                return $acs;
            }
        }

        $this->getLogger()->error(
            'No supported binding found for ACS',
            array('acs' => $serviceProvider->assertionConsumerServices)
        );

        throw new EngineBlock_Corto_ProxyServer_Exception('No supported binding found for ACS');
    }

    /**
     * Returns a custom ACS location from request or false when
     * none is specified
     *
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     * @param ServiceProvider $serviceProvider
     * @return null|\OpenConext\EngineBlock\Metadata\IndexedService
     */
    public function getCustomAssertionConsumer(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        ServiceProvider $serviceProvider
    ) {
        $requestWasSigned = $request->wasSigned();

        /** @var AuthnRequest $request */

        // Ignore requests for bindings we don't support for responses.
        $protocolBinding = $request->getProtocolBinding();
        // ProtocolBinding is not mandatory, seems safe to assume HTTP-POST since it's
        // the only thing EB supports anyway.
        if (empty($protocolBinding)) {
            $protocolBinding = Constants::BINDING_HTTP_POST;
        }

        if ($protocolBinding !== Constants::BINDING_HTTP_POST) {
            $this->_server->getLogger()->notice(
                "ProtocolBinding '{$protocolBinding}' requested is not supported, ignoring..."
            );
            return false;
        }

        // Custom ACS Location & ProtocolBinding goes first
        if ($request->getAssertionConsumerServiceURL()) {
            if ($requestWasSigned) {
                $this->_server->getLogger()->info(
                    "Using AssertionConsumerServiceLocation '{$request->getAssertionConsumerServiceURL()}' ".
                    "and ProtocolBinding '{$protocolBinding}' from signed request. "
                );
                return new Service($request->getAssertionConsumerServiceURL(), $protocolBinding);
            }
            else {
                $requestAcsIsRegisteredInMetadata = false;
                foreach ($serviceProvider->assertionConsumerServices as $entityAcs) {
                    $requestAcsIsRegisteredInMetadata = (
                        $entityAcs->location === $request->getAssertionConsumerServiceURL() &&
                        $entityAcs->binding === $protocolBinding
                    );
                    if ($requestAcsIsRegisteredInMetadata) {
                        break;
                    }
                }
                if ($requestAcsIsRegisteredInMetadata) {
                    $this->_server->getLogger()->info(
                        "Using AssertionConsumerServiceLocation '{$request->getAssertionConsumerServiceURL()}' ".
                        "and ProtocolBinding '{$protocolBinding}' from unsigned request, ".
                        "it's okay though, the ACSLocation and Binding were registered in the metadata"
                    );
                    return new Service($request->getAssertionConsumerServiceURL(), $protocolBinding);
                }
                else {
                    $this->_server->getLogger()->notice(
                        "AssertionConsumerServiceLocation '{$request->getAssertionConsumerServiceURL()}' ".
                        "and ProtocolBinding '{$protocolBinding}' were mentioned in request, ".
                        "but the AuthnRequest was not signed, and the ACSLocation and Binding were not found in ".
                        "the metadata for the SP, so I am disallowed from acting upon it.".
                        "Trying the default endpoint.."
                    );
                }
            }
            return false;
        }

        if ($request->getAssertionConsumerServiceIndex()) {
            $index = (int)$request->getAssertionConsumerServiceIndex();

            // Find the indexed ACS in the metadata.
            $indexedAssertionConsumerService = null;
            foreach ($serviceProvider->assertionConsumerServices as $assertionConsumerService) {
                if ((int)$assertionConsumerService->serviceIndex === $index) {
                    $indexedAssertionConsumerService = $assertionConsumerService;
                    break;
                }
            }

            if ($indexedAssertionConsumerService) {
                $this->_server->getLogger()->info(
                    "Using AssertionConsumerServiceIndex '$index' from request"
                );
                return $indexedAssertionConsumerService;
            }
            else {
                $this->_server->getLogger()->notice(
                    "AssertionConsumerServiceIndex was mentioned in request, but we don't know any ACS by ".
                    "index '$index'? Maybe the metadata was updated and we don't have that endpoint yet? ".
                    "Trying the default endpoint.."
                );
            }
        }
    }

    public function sendResponseToRequestIssuer(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        EngineBlock_Saml2_ResponseAnnotationDecorator $response
    ) {
        /** @var AuthnRequest $request */
        $requestIssuer = $request->getIssuer() ? $request->getIssuer()->getValue() : '';
        $serviceProvider = $this->getRepository()->fetchServiceProviderByEntityId($requestIssuer);

        // Detect error responses and send them off without an assertion.
        /** @var Response $response */
        $status = $response->getStatus();
        if ($status['Code'] !== 'urn:oasis:names:tc:SAML:2.0:status:Success') {
            $response->setAssertions(array());
            $this->getBindingsModule()->send($response, $serviceProvider);
            return;
        }

        $this->filterOutputAssertionAttributes($response, $request);
        $this->getBindingsModule()->send($response, $serviceProvider);
    }

    /**
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $response
     * @return EngineBlock_Saml2_AuthnRequestAnnotationDecorator
     * @throws EngineBlock_Corto_ProxyServer_Exception
     * @throws EngineBlock_Exception
     * @throws EngineBlock_Corto_Module_Services_SessionLostException
     */
    public function getReceivedRequestFromResponse(EngineBlock_Saml2_ResponseAnnotationDecorator $response)
    {
        /** @var Response $response */
        $requestId = $response->getInResponseTo();
        if ($requestId === null) {
            throw new EngineBlock_Corto_ProxyServer_Exception(
                'Response without InResponseTo, e.g. unsolicited. We don\'t support this.',
                EngineBlock_Exception::CODE_NOTICE
            );
        }
        return $this->findRequestFromRequestId($requestId);
    }

    public function findRequestFromRequestId(string $requestId): ?EngineBlock_Saml2_AuthnRequestAnnotationDecorator
    {
        $authnRequestRepository = new EngineBlock_Saml2_AuthnRequestSessionRepository($this->getLogger());

        $spRequestId = $authnRequestRepository->findLinkedRequestId($requestId);
        if ($spRequestId === null) {
            throw new EngineBlock_Corto_Module_Services_SessionLostException(
                sprintf(
                    'Trying to find an AuthnRequest (we made and sent) with id "%s" but it is not known in this session. '.
                    'Likely the user lost their session.',
                    $requestId
                ),
                EngineBlock_Corto_ProxyServer_Exception::CODE_NOTICE
            );
        }

        $spRequest = $authnRequestRepository->findRequestById($spRequestId);
        if (!$spRequest) {
            throw new EngineBlock_Corto_ProxyServer_Exception(
                'Response has no known Request',
                EngineBlock_Corto_ProxyServer_Exception::CODE_NOTICE
            );
        }

        return $spRequest;
    }

////////  ATTRIBUTE FILTERING /////////

    public function filterInputAssertionAttributes(
        EngineBlock_Saml2_ResponseAnnotationDecorator &$response,
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
    ) {
        $this->callAttributeFilter(
            array(new EngineBlock_Corto_Filter_Input($this), 'filter'),
            $response,
            $request,
            $this->getRepository()->fetchServiceProviderByEntityId($request->getIssuer()->getValue()),
            $this->getRepository()->fetchIdentityProviderByEntityId($response->getIssuer()->getValue())
        );
    }

    public function filterOutputAssertionAttributes(
        EngineBlock_Saml2_ResponseAnnotationDecorator &$response,
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
    ) {
        $this->callAttributeFilter(
            array(new EngineBlock_Corto_Filter_Output($this), 'filter'),
            $response,
            $request,
            $this->getRepository()->fetchServiceProviderByEntityId($request->getIssuer()->getValue()),
            $this->getRepository()->fetchIdentityProviderByEntityId($response->getOriginalIssuer())
        );
    }

    protected function callAttributeFilter(
        $callback,
        EngineBlock_Saml2_ResponseAnnotationDecorator &$response,
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        ServiceProvider $spEntityMetadata,
        IdentityProvider $idpEntityMetadata
    ) {
        // Take em out
        $responseAttributes = $response->getAssertion()->getAttributes();

        // Call the filter
        call_user_func_array(
            $callback,
            array(&$response, &$responseAttributes, $request, $spEntityMetadata, $idpEntityMetadata)
        );

        // Put em back where they belong
        $response->getAssertion()->setAttributes($responseAttributes);
    }

//////// I/O /////////

    /**
     * Parse the HTTP URL query string and return the (raw) parameters in an array.
     *
     * We need to do this ourselves, so that we get access to the raw (url encoded) values.
     * This is required because different software can url encode to different values.
     *
     * @return array Raw parameters form the query string
     */
    public function getRawGet()
    {
        $rawGet = array();
        foreach (explode("&", $_SERVER['QUERY_STRING']) as $parameter) {
            if (preg_match("/^(.+)=(.*)$/", $parameter, $keyAndValue)) {
                $rawGet[$keyAndValue[1]] = $keyAndValue[2];
            }
        }
        return $rawGet;
    }

    public function redirect($location, $message)
    {
        $this->getLogger()->info("Redirecting to $location");
        $this->sendHeader('Location', $location);
    }

    public function setCookie($name, $value, $expire = null, $path = null, $domain = null, $secure = null, $httpOnly = null)
    {
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

    public function getCookie($name, $defaultValue = null)
    {
        if (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        }
        return $defaultValue;
    }

//////// UTILITIES /////////

    /**
     * Sign a Corto_XmlToArray array with XML.
     *
     * @param EngineBlock_Corto_XmlToArray $element Element to sign
     * @return array Signed element
     */
    public function sign(array $element)
    {
        $signingKeyPair = $this->getSigningCertificates();

        $signature = array(
            '__t' => 'ds:Signature',
            '_xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
            'ds:SignedInfo' => array(
                '__t' => 'ds:SignedInfo',
                '_xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                'ds:CanonicalizationMethod' => array(
                    '_Algorithm' => 'http://www.w3.org/2001/10/xml-exc-c14n#',
                ),
                'ds:SignatureMethod' => array(
                    '_Algorithm' => 'http://www.w3.org/2000/09/xmldsig#rsa-sha1',
                ),
                'ds:Reference' => array(
                    0 => array(
                        '_URI' => '__placeholder__',
                        'ds:Transforms' => array(
                            'ds:Transform' => array(
                                array(
                                    '_Algorithm' => 'http://www.w3.org/2000/09/xmldsig#enveloped-signature',
                                ),
                                array(
                                    '_Algorithm' => 'http://www.w3.org/2001/10/xml-exc-c14n#',
                                ),
                            ),
                        ),
                        'ds:DigestMethod' => array(
                            '_Algorithm' => 'http://www.w3.org/2000/09/xmldsig#sha1',
                        ),
                        'ds:DigestValue' => array(
                            '__v' => '__placeholder__',
                        ),
                    ),
                ),
            ),
            'ds:SignatureValue' => array(
                '__v' => '__placeholder__',
            ),
            'ds:KeyInfo' => array(
                'ds:X509Data' => array(
                    'ds:X509Certificate' => array(
                        '__v' => $signingKeyPair->getCertificate()->toCertData(),
                    ),
                ),
            ),
        );

        // Convert the XMl object to actual XML and get a reference to what we're about to sign
        $canonicalXmlDom = DOMDocumentFactory::create();
        $canonicalXmlDom->loadXML(EngineBlock_Corto_XmlToArray::array2xml($element));

        // Note that the current element may not be the first or last, because we might include comments, so look for
        // the actual XML element
        $xpath = new DOMXPath($canonicalXmlDom);
        $nodes = $xpath->query('/*[@ID="'.$element['_ID'].'"]');
        if ($nodes->length < 1) {
            throw new EngineBlock_Corto_ProxyServer_Exception(
                "Unable to sign message can't find element with id to sign?",
                EngineBlock_Corto_ProxyServer_Exception::CODE_NOTICE
            );
        }
        $canonicalXmlDom = $nodes->item(0);
        // Now do 'exclusive no-comments' XML cannonicalization
        $canonicalXml = $canonicalXmlDom->C14N(true, false);

        // Hash it, encode it in Base64 and include that as the 'Reference'
        $signature['ds:SignedInfo']['ds:Reference'][0]['ds:DigestValue']['__v'] = base64_encode(sha1($canonicalXml, true));
        $signature['ds:SignedInfo']['ds:Reference'][0]['_URI'] = "#".$element['_ID'];

        // Now we start the actual signing, instead of signing the entire (possibly large) document,
        // we only sign the 'SignedInfo' which includes the 'Reference' hash
        $canonicalXml2Dom = DOMDocumentFactory::fromString(
            EngineBlock_Corto_XmlToArray::array2xml($signature['ds:SignedInfo'])
        );
        $canonicalXml2 = $canonicalXml2Dom->firstChild->C14N(true, false);

        $signatureValue = $signingKeyPair->getPrivateKey()->sign($canonicalXml2);

        $signature['ds:SignatureValue']['__v'] = base64_encode($signatureValue);

        $element['ds:Signature'] = $signature;
        $element[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Signed'] = true;

        return $element;
    }

    /**
     * Log and verify the signature methods used.
     *
     * The signatures are not validated here, but the used methods
     * (algorithms) are logged and an error is thrown if they're explicitly
     * prohibited from use.
     *
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $receivedResponse
     */
    public function checkResponseSignatureMethods(EngineBlock_Saml2_ResponseAnnotationDecorator $receivedResponse)
    {
        $issuer = $receivedResponse->getIssuer()->getValue();
        $log = EngineBlock_ApplicationSingleton::getInstance()
            ->getLogInstance();

        $log->notice(
            sprintf(
                'Received Assertion from Issuer "%s" with signature method algorithms Response: "%s" and Assertion: "%s"',
                $issuer,
                $receivedResponse->getSignatureMethod(),
                $receivedResponse->getAssertion()->getSignatureMethod()
            )
        );

        if ($receivedResponse->getSignatureMethod() !== null) {
            $this->assertSignatureMethodIsAllowed($receivedResponse->getSignatureMethod());
        }

        if ($receivedResponse->getAssertion()->getSignatureMethod() !== null) {
            $this->assertSignatureMethodIsAllowed($receivedResponse->getAssertion()->getSignatureMethod());
        }
    }

    /**
     * @param string $signatureMethod
     * @throws EngineBlock_Corto_Module_Bindings_Exception
     */
    private function assertSignatureMethodIsAllowed($signatureMethod)
    {
        if (in_array($signatureMethod, $this->getConfig('forbiddenSignatureMethods'))) {
            throw new EngineBlock_Corto_Module_Bindings_UnsupportedSignatureMethodException($signatureMethod);
        }
    }

    /**
     * For a given url hosted by this Corto installation, get the EntityCode, remoteIdPMd5Hash and ServiceName.
     *
     * Gets the PATH_INFO from a URL like: http://host/path/corto.php/path/info
     *
     * @param string $url
     * @return array Parameters: EntityCode, ServiceName and RemoteIdPMd5Hash
     * @throws EngineBlock_Corto_ProxyServer_Exception
     */
    public function getParametersFromUrl($url)
    {
        $parameters = array(
            'EntityCode' => 'main',
            'ServiceName' => '',
            'RemoteIdPMd5Hash' => '',
        );
        $urlPath = parse_url($url, PHP_URL_PATH); // /authentication/x/ServiceName[/remoteIdPMd5Hash]

        foreach ($this->_serviceToControllerMapping as $serviceName => $controllerUri) {
            if (strstr($urlPath, $controllerUri)) {
                $urlPath = str_replace($controllerUri, $serviceName, $urlPath);
                $urlParts = explode('/', $urlPath);
                $parameters['ServiceName'] = array_shift($urlParts);
                if (isset($urlParts[0])) {
                    $parameters['RemoteIdPMd5Hash'] = array_shift($urlParts);
                }
                return $parameters;
            }
        }

        throw new EngineBlock_Corto_ProxyServer_Exception(sprintf('Unable to map URL "%s" to EngineBlock URL', $url));
    }

    /**
     * Generate a SAML datetime with a given delta in seconds.
     *
     * Delta 0 gives current date and time, delta 3600 is +1 hour, delta -3600 is -1 hour.
     *
     * @param int $deltaSeconds
     * @param int|null $time Current time to add delta to.
     * @return string
     */
    public function timeStamp($deltaSeconds = 0)
    {
        $provider = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getTimeProvider();
        return $provider->timestamp($deltaSeconds);
    }

    public function getNewId($usage = EngineBlock_Saml2_IdGenerator::ID_USAGE_OTHER)
    {
        $generator = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getSaml2IdGenerator();
        return $generator->generate(self::ID_PREFIX, $usage);
    }

    public function startSession()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * @param $certificates
     * @return resource
     * @throws EngineBlock_Corto_ProxyServer_Exception
     */
    public function getPrivateKeyFromCertificates($certificates)
    {
        if (!empty($certificates['private'])) {
            $privateKeyPem = $certificates['private'];
        }
        else if (!empty($certificates['privateFile'])) {
                if (!file_exists($certificates['privateFile'])) {
                    throw new EngineBlock_Corto_ProxyServer_Exception(
                        sprintf('Private key PEM not found at: "%s"', $certificates['privateFile'])
                    );
                }
                $privateKeyPem = file_get_contents($certificates['privateFile']);
        }
        else {
                throw new EngineBlock_Corto_ProxyServer_Exception(
                    'Current entity has no private key, unable to sign message! Please set ["certificates"]["privateFile"]!',
                    EngineBlock_Exception::CODE_WARNING
                );
            }

        $privateKey = openssl_pkey_get_private($privateKeyPem);
        if ($privateKey === false) {
            throw new EngineBlock_Corto_ProxyServer_Exception(
                sprintf(
                    "Current entity ['certificates']['private'] value is NOT a valid PEM formatted SSL private key? ".
                    "Value: '%s'",
                    $privateKeyPem
                )
            );
        }
        return $privateKey;
    }

    /**
     * Find a ServiceProvider instance based on the received AuthnRequest.
     *
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     * @param LoggerInterface $logger
     * @return ServiceProvider
     */
    public function findOriginalServiceProvider(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        LoggerInterface $logger
    ) {

        $issuingServiceProvider = $this->getRepository()->fetchServiceProviderByEntityId($request->getIssuer()->getValue());

        // Find the original requester SP
        $originalRequesterServiceProvider = EngineBlock_SamlHelper::findRequesterServiceProvider(
            $issuingServiceProvider,
            $request,
            $this->getRepository(),
            $logger
        );

        // If the SamlHelper found us the correct SP, return it
        if ($originalRequesterServiceProvider) {
            return $originalRequesterServiceProvider;
        }

        // Return the SP based on the Issuer.
        return $issuingServiceProvider;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->_logger = $logger;
    }

    /**
     * Getter for the Logger. If the logger is not yet present it is loaded from the ApplicationSingleton.
     * @return LoggerInterface
     */
    public function getLogger()
    {
        if ($this->_logger instanceof LoggerInterface) {
            return $this->_logger;
        }
        $this->_logger = EngineBlock_ApplicationSingleton::getLog();
        return $this->_logger;
    }

}
