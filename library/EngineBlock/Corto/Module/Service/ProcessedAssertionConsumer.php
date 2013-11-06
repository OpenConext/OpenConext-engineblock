<?php

class EngineBlock_Corto_Module_Service_ProcessedAssertionConsumer extends EngineBlock_Corto_Module_Service_Abstract
{
    public function serve($serviceName)
    {
        $response = $this->_server->getBindingsModule()->receiveResponse();
        $receivedRequest = $this->_server->getReceivedRequestFromResponse($response->getInResponseTo());

        $remainingProcessingEntities = &$_SESSION['Processing'][$receivedRequest->getId()]['RemainingEntities'];

        // @todo check if this is the correct place to flush log
        // Flush log if SP or IdP has additional logging enabled
        $sp = $this->_server->getRemoteEntity($receivedRequest->getIssuer());
        $idp = $this->_server->getRemoteEntity(EngineBlock_SamlHelper::extractOriginalIssuerFromMessage($response));
        if (EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging($sp, $idp)) {
            EngineBlock_ApplicationSingleton::getInstance()->getLogInstance()->flushQueue();
        }

        if (!empty($remainingProcessingEntities)) { // Moar processing!
            $nextProcessingEntity = array_shift($remainingProcessingEntities);

            $this->_server->setProcessingMode();

            $newResponse = $this->_server->createEnhancedResponse($receivedRequest, $response);

            // Change the destiny of the received response
            $newResponse->setId($response->getId());
            $newResponse->setDestination($nextProcessingEntity['Location']);
            $newResponse->setDeliverByBinding($nextProcessingEntity['Binding']);
            $newResponse->setReturn($this->_server->getUrl('processedAssertionConsumerService'));

            $this->_server->getBindingsModule()->send($newResponse, $nextProcessingEntity);
            return;
        }
        else { // Done processing! Send off to SP
            $response->setDestination($_SESSION['Processing'][$receivedRequest->getId()]['OriginalDestination']);
            $response->setDeliverByBinding($_SESSION['Processing'][$receivedRequest->getId()]['OriginalBinding']);
            $response->setOriginalIssuer($_SESSION['Processing'][$receivedRequest->getId()]['OriginalIssuer']);

            $attributes = $response->getAssertion()->getAttributes();
            unset($attributes['urn:org:openconext:corto:internal:sp-entity-id']);
            $response->getAssertion()->setAttributes($attributes);

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
