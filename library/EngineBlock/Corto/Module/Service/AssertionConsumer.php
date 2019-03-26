<?php

use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
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

        $this->checkResponseSignatureMethods($receivedResponse);

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
            $requestId = $receivedResponse->getInResponseTo();

            // Authentication state needs to be registered here as the debug flow differs from the regular flow,
            // yet the procedures for both are completed when consuming the assertion in the ServiceProviderController
            $identityProvider = new Entity(new EntityId($idp->entityId), EntityType::IdP());
            $authenticationState = $this->_session->get('authentication_state');
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
            $inResponseTo = $receivedResponse->getInResponseTo();
            $newResponse->setInResponseTo($inResponseTo);
            $newResponse->setDestination($firstProcessingEntity->responseProcessingService->location);
            $newResponse->setDeliverByBinding($firstProcessingEntity->responseProcessingService->binding);
            $newResponse->setReturn($this->_server->getUrl('processedAssertionConsumerService'));

            $identityProvider = new Entity(new EntityId($idp->entityId), EntityType::IdP());
            $authenticationState = $this->_session->get('authentication_state');
            $authenticationState->authenticatedAt($inResponseTo, $identityProvider);

            $this->_server->getBindingsModule()->send($newResponse, $firstProcessingEntity);
        }
        else {
            $newResponse = $this->_server->createEnhancedResponse($receivedRequest, $receivedResponse);
            $this->_server->sendResponseToRequestIssuer($receivedRequest, $newResponse);
        }
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
    private function checkResponseSignatureMethods(EngineBlock_Saml2_ResponseAnnotationDecorator $receivedResponse)
    {
        $issuer = $receivedResponse->getIssuer();
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
        if (in_array($signatureMethod, $this->_server->getConfig('forbiddenSignatureMethods'))) {
            throw new EngineBlock_Corto_Module_Bindings_UnsupportedSignatureMethodException($signatureMethod);
        }
    }
}
