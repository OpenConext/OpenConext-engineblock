<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractConfigurationEntity;

class EngineBlock_Corto_Module_Service_ProcessedAssertionConsumer extends EngineBlock_Corto_Module_Service_Abstract
{
    public function serve($serviceName)
    {
        $response = $this->_server->getBindingsModule()->receiveResponse();
        $receivedRequest = $this->_server->getReceivedRequestFromResponse($response);

        if ($receivedRequest->getKeyId()) {
            $this->_server->setKeyId($receivedRequest->getKeyId());
        }

        $remainingProcessingEntities = &$_SESSION['Processing'][$receivedRequest->getId()]['RemainingEntities'];

        // @todo check if this is the correct place to flush log
        // Flush log if SP or IdP has additional logging enabled
        $sp  = $this->_server->getRepository()->fetchServiceProviderByEntityId($receivedRequest->getIssuer());
        $idp = $this->_server->getRepository()->fetchIdentityProviderByEntityId($response->getOriginalIssuer());
        if (
            $this->_server->getConfig('debug', false) ||
            EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging(array($sp, $idp))
        ) {
            EngineBlock_ApplicationSingleton::getInstance()->getLogInstance()->flushQueue();
        }

        if (!empty($remainingProcessingEntities)) { // Moar processing!
            /** @var AbstractConfigurationEntity $nextProcessingEntity */
            $nextProcessingEntity = array_shift($remainingProcessingEntities);

            $this->_server->setProcessingMode();

            $newResponse = $this->_server->createEnhancedResponse($receivedRequest, $response);

            // Change the destiny of the received response
            $newResponse->setId($response->getId());
            $newResponse->setDestination($nextProcessingEntity->responseProcessingService->location);
            $newResponse->setDeliverByBinding($nextProcessingEntity->responseProcessingService->binding);
            $newResponse->setReturn($this->_server->getUrl('processedAssertionConsumerService'));

            $this->_server->getBindingsModule()->send($newResponse, $nextProcessingEntity);
            return;
        }
        else { // Done processing! Send off to SP
            $response->setDestination($_SESSION['Processing'][$receivedRequest->getId()]['OriginalDestination']);
            $response->setDeliverByBinding($_SESSION['Processing'][$receivedRequest->getId()]['OriginalBinding']);
            $response->setOriginalIssuer($_SESSION['Processing'][$receivedRequest->getId()]['OriginalIssuer']);

            $this->_server->unsetProcessingMode();

            // Cache the response
            EngineBlock_Corto_Model_Response_Cache::cacheResponse(
                $receivedRequest,
                $response,
                EngineBlock_Corto_Model_Response_Cache::RESPONSE_CACHE_TYPE_OUT
            );

            $sentResponse = $this->_server->createEnhancedResponse($receivedRequest, $response);
            $this->_server->sendResponseToRequestIssuer($receivedRequest, $sentResponse);
            return;
        }
    }
}
