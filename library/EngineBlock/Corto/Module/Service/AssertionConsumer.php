<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractRole;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use Symfony\Component\HttpFoundation\Session\Session;

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

    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        EngineBlock_Corto_XmlToArray $xmlConverter,
        Session $session
    ) {
        $this->_server = $server;
        $this->_xmlConverter = $xmlConverter;
        $this->_session = $session;
    }

    /**
     * @param $serviceName
     */
    public function serve($serviceName)
    {
        $receivedResponse = $this->_server->getBindingsModule()->receiveResponse();
        $receivedRequest = $this->_server->getReceivedRequestFromResponse($receivedResponse);

        $application = EngineBlock_ApplicationSingleton::getInstance();
        $log = $application->getLogInstance();

        $log->notice(sprintf(
            'Received Assertion from Issuer "%s" with signature method algorithm "%s"',
            $receivedResponse->getIssuer(),
            $receivedResponse->getSignatureMethod()
        ));

        $sp  = $this->_server->getRepository()->fetchServiceProviderByEntityId($receivedRequest->getIssuer());

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
            $log->info('Raw HTTP request', array('http_request' => (string) $application->getHttpRequest()));
        }

        if ($receivedRequest->isDebugRequest()) {
            $_SESSION['debugIdpResponse'] = $receivedResponse;

            // Authentication state needs to be registered here as the debug flow differs from the regular flow,
            // yet the procedures for both are completed when consuming the assertion in the ServiceProviderController
            $identityProvider = new Entity(new EntityId($idp->entityId), EntityType::IdP());
            $authenticationState = $this->_session->get('authentication_state');
            $authenticationState->authenticatedAt($identityProvider);

            $this->_server->redirect(
                $this->_server->getUrl('debugSingleSignOnService'),
                'Show original Response from IDP'
            );
            return;
        }

        if ($receivedRequest->getKeyId()) {
            $this->_server->setKeyId($receivedRequest->getKeyId());
        }

        // Cache the response
        EngineBlock_Corto_Model_Response_Cache::cacheResponse(
            $receivedRequest,
            $receivedResponse,
            EngineBlock_Corto_Model_Response_Cache::RESPONSE_CACHE_TYPE_IN
        );

        $this->_server->filterInputAssertionAttributes($receivedResponse, $receivedRequest);

        $processingEntities = $this->_server->getConfig('Processing', array());
        if (!empty($processingEntities)) {
            /** @var AbstractRole $firstProcessingEntity */
            $firstProcessingEntity = array_shift($processingEntities);
            $_SESSION['Processing'][$receivedRequest->getId()]['RemainingEntities']   = $processingEntities;
            $_SESSION['Processing'][$receivedRequest->getId()]['OriginalDestination'] = $receivedResponse->getDestination();
            $_SESSION['Processing'][$receivedRequest->getId()]['OriginalIssuer']      = $receivedResponse->getOriginalIssuer();
            $_SESSION['Processing'][$receivedRequest->getId()]['OriginalBinding']     = $receivedResponse->getOriginalBinding();

            $this->_server->setProcessingMode();
            $newResponse = $this->_server->createEnhancedResponse($receivedRequest, $receivedResponse);

            // Change the destiny of the received response
            $newResponse->setInResponseTo($receivedResponse->getInResponseTo());
            $newResponse->setDestination($firstProcessingEntity->responseProcessingService->location);
            $newResponse->setDeliverByBinding($firstProcessingEntity->responseProcessingService->binding);
            $newResponse->setReturn($this->_server->getUrl('processedAssertionConsumerService'));

            $identityProvider = new Entity(new EntityId($idp->entityId), EntityType::IdP());
            $authenticationState = $this->_session->get('authentication_state');
            $authenticationState->authenticatedAt($identityProvider);

            $this->_server->getBindingsModule()->send($newResponse, $firstProcessingEntity);
        }
        else {
            $newResponse = $this->_server->createEnhancedResponse($receivedRequest, $receivedResponse);
            $this->_server->sendResponseToRequestIssuer($receivedRequest, $newResponse);
        }
    }
}
