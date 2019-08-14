<?php

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Service\ProcessingStateHelperInterface;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationState;
use OpenConext\EngineBlockBundle\Sfo\SfoGatewayCallOutHelper;
use OpenConext\EngineBlockBundle\Sfo\SfoIdentityProvider;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

class EngineBlock_Corto_Module_Service_AssertionConsumer implements EngineBlock_Corto_Module_Service_ServiceInterface
{
    /**
     * @var EngineBlock_Corto_ProxyServer
     */
    private $_server;

    /**
     * @var EngineBlock_Corto_XmlToArray
     */
    private $_xmlConverter;

    /**
     * @var Session
     */
    private $_session;

    /**
     * @var ProcessingStateHelperInterface
     */
    private $_processingStateHelper;
    /**
     * @var SfoGatewayCallOutHelper
     */
    private $_sfoGatewayCallOutHelper;

    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        EngineBlock_Corto_XmlToArray $xmlConverter,
        Session $session,
        ProcessingStateHelperInterface $processingStateHelper,
        SfoGatewayCallOutHelper $sfoGatewayCallOutHelper
    ) {
        $this->_server = $server;
        $this->_xmlConverter = $xmlConverter;
        $this->_session = $session;
        $this->_processingStateHelper = $processingStateHelper;
        $this->_sfoGatewayCallOutHelper = $sfoGatewayCallOutHelper;
    }

    /**
     * @param $serviceName
     * @param Request $httpRequest
     */
    public function serve($serviceName, Request $httpRequest)
    {
        $serviceEntityId = $this->_server->getUrl('assertionConsumerService');
        $receivedResponse = $this->_server->getBindingsModule()->receiveResponse($serviceEntityId, $serviceEntityId);
        $receivedRequest = $this->_server->getReceivedRequestFromResponse($receivedResponse);

        $application = EngineBlock_ApplicationSingleton::getInstance();
        $log = $application->getLogInstance();

        $this->_server->checkResponseSignatureMethods($receivedResponse);

        $sp = $this->_server->getRepository()->fetchServiceProviderByEntityId($receivedRequest->getIssuer());

        // Verify the SP requester chain.
        EngineBlock_SamlHelper::getSpRequesterChain(
            $sp,
            $receivedRequest,
            $this->_server->getRepository()
        );

        // Flush log if SP or IdP has additional logging enabled
        $idp = $this->_server->getRepository()->fetchIdentityProviderByEntityId($receivedResponse->getIssuer());

        if (EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging(array($sp, $idp))) {
            $application->flushLog('Activated additional logging for the SP or IdP');
            $log->info('Raw HTTP request', array('http_request' => (string)$application->getHttpRequest()));
        }

        if ($receivedRequest->isDebugRequest()) {
            $_SESSION['debugIdpResponse'] = $receivedResponse;
            $requestId = $receivedResponse->getInResponseTo();

            // Authentication state needs to be registered here as the debug flow differs from the regular flow,
            // yet the procedures for both are completed when consuming the assertion in the ServiceProviderController
            $identityProvider = new Entity(new EntityId($idp->entityId), EntityType::IdP());
            $authenticationState = $this->getAuthenticationState();
            $authenticationState->authenticatedAt($requestId, $identityProvider);

            $this->_server->redirect(
                $this->_server->getUrl('debugSingleSignOnService'),
                'Show original Response from IDP'
            );
            return;
        }

        if ($receivedRequest->getKeyId()) {
            $this->_server->setKeyId($receivedRequest->getKeyId());
        }

        // Keep track of what IDP was used for this SP. This way the user does
        // not have to go trough the WAYF again when logging into this service
        // or another service.
        EngineBlock_Corto_Model_Response_Cache::rememberIdp($receivedRequest, $receivedResponse);

        $this->_server->filterInputAssertionAttributes($receivedResponse, $receivedRequest);

        // Add the consent step
        $currentProcessStep = $this->_processingStateHelper->addStep(
            $receivedRequest->getId(),
            ProcessingStateHelperInterface::STEP_CONSENT,
            $this->getEngineSpRole($this->_server),
            $receivedResponse
        );

        // Goto consent if no SFO needed
        if (!$this->_sfoGatewayCallOutHelper->shouldUseSfo($idp, $sp)) {
            $this->_server->sendConsentAuthenticationRequest($receivedResponse, $receivedRequest, $currentProcessStep->getRole(), $this->getAuthenticationState());
            return;
        }

        // Update AuthnClassRef and NameId
        $nameId = clone $receivedResponse->getNameId();
        $authnClassRef = $this->_sfoGatewayCallOutHelper->getSfoLoa($idp, $sp);

        // Add sfo step
        $currentProcessStep = $this->_processingStateHelper->addStep(
            $receivedRequest->getId(),
            ProcessingStateHelperInterface::STEP_SFO,
            $application->getDiContainer()->getSfoIdentityProvider($this->_server),
            $receivedResponse
        );

        $this->_server->sendSfoAuthenticationRequest($receivedRequest, $currentProcessStep->getRole(), $authnClassRef, $nameId);
    }

    /**
     * @return AuthenticationState
     */
    private function getAuthenticationState()
    {
        return $this->_session->get('authentication_state');
    }

    /**
     * @param EngineBlock_Corto_ProxyServer $proxyServer
     * @return ServiceProvider
     * @throws EngineBlock_Corto_ProxyServer_Exception
     * @throws EngineBlock_Exception
     */
    protected function getEngineSpRole(EngineBlock_Corto_ProxyServer $proxyServer)
    {
        $spEntityId = $proxyServer->getUrl('spMetadataService');
        $engineServiceProvider = $proxyServer->getRepository()->findServiceProviderByEntityId($spEntityId);
        if (!$engineServiceProvider) {
            throw new EngineBlock_Exception(
                sprintf(
                    "Unable to find EngineBlock configured as Service Provider. No '%s' in repository!",
                    $spEntityId
                )
            );
        }

        return $engineServiceProvider;
    }
}
