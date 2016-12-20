<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractRole;

class EngineBlock_Corto_Module_Service_AssertionConsumer extends EngineBlock_Corto_Module_Service_Abstract
{
    public function serve($serviceName)
    {
        $receivedResponse = $this->_server->getBindingsModule()->receiveResponse();
        $receivedRequest = $this->_server->getReceivedRequestFromResponse($receivedResponse);

        $application = EngineBlock_ApplicationSingleton::getInstance();
        $log = $application->getLogInstance();

        $log->notice(sprintf(
            'Received Assertion from Issuer "%s" with signature method algorithm "%s"',
            $receivedResponse->getIssuer(),
            $receivedResponse->getAssertion()->getSignatureMethod()
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

        // Exposing entityId for further processing
        $application->authenticationStateIdpEntityId = $idp->entityId;

        if (EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging(array($sp, $idp))) {
            $application->flushLog('Activated additional logging for the SP or IdP');
            $log->info('Raw HTTP request', array('http_request' => (string) $application->getHttpRequest()));
        }

        if ($receivedRequest->isDebugRequest()) {
            $_SESSION['debugIdpResponse'] = $receivedResponse;
            $this->_server->redirect($this->_server->getUrl('debugSingleSignOnService'), 'Show original Response from IDP');
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

            $this->_server->getBindingsModule()->send($newResponse, $firstProcessingEntity);
        }
        else {
            $newResponse = $this->_server->createEnhancedResponse($receivedRequest, $receivedResponse);
            $this->_server->sendResponseToRequestIssuer($receivedRequest, $newResponse);
        }
    }
}
