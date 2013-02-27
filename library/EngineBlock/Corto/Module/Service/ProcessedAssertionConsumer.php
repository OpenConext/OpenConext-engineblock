<?php

class EngineBlock_Corto_Module_Service_ProcessedAssertionConsumer extends EngineBlock_Corto_Module_Service_Abstract
{
    public function serve($serviceName)
    {
        $response = $this->_server->getBindingsModule()->receiveResponse();
        $receivedRequest = $this->_server->getReceivedRequestFromResponse($response[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'InResponseTo']);

        $remainingProcessingEntities = &$_SESSION['Processing'][$receivedRequest[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['RemainingEntities'];

        // @todo check if this is the correct place to flush log
        // Flush log if SP or IdP has additional logging enabled
        $sp = $this->_server->getRemoteEntity(EngineBlock_SamlHelper::extractIssuerFromMessage($receivedRequest));
        $idp = $this->_server->getRemoteEntity(EngineBlock_SamlHelper::extractOriginalIssuerFromMessage($response));
        if (EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging($sp, $idp)) {
            EngineBlock_ApplicationSingleton::getInstance()->getLogInstance()->flushQueue();
        }

        if (!empty($remainingProcessingEntities)) { // Moar processing!
            $nextProcessingEntity = array_shift($remainingProcessingEntities);

            $this->_server->setProcessingMode();

            $newResponse = $this->_server->createEnhancedResponse($receivedRequest, $response);

            // Change the destiny of the received response
            $newResponse[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']                    = $response[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID'];
            $newResponse[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Destination']           = $nextProcessingEntity['Location'];
            $newResponse[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['ProtocolBinding']  = $nextProcessingEntity['Binding'];
            $newResponse[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Return']           = $this->_server->getUrl('processedAssertionConsumerService');
            $newResponse[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['paramname']        = 'SAMLResponse';

            $this->_server->getBindingsModule()->send($newResponse, $nextProcessingEntity);
            return;
        }
        else { // Done processing! Send off to SP
            $response[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Destination']          = $_SESSION['Processing'][$receivedRequest[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['OriginalDestination'];
            $response[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['ProtocolBinding'] = $_SESSION['Processing'][$receivedRequest[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['OriginalBinding'];
            $response[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['OriginalIssuer']  = $_SESSION['Processing'][$receivedRequest[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['OriginalIssuer'];

            $responseAssertionAttributes = &$response['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute'];
            $attributes = $this->_xmlConverter->attributesToArray($responseAssertionAttributes);
            unset($attributes['urn:org:openconext:corto:internal:sp-entity-id']);
            $responseAssertionAttributes = EngineBlock_Corto_XmlToArray::array2attributes($attributes);

            $this->_server->unsetProcessingMode();

            // Cache the response
            EngineBlock_Corto_Model_Response_Cache::cacheResponse(
                $receivedRequest,
                $response,
                EngineBlock_Corto_Model_Response_Cache::RESPONSE_CACHE_TYPE_OUT,
                $this->_server->getVirtualOrganisationContext()
            );

            $sentResponse = $this->_server->createEnhancedResponse($receivedRequest, $response);
            $this->_server->sendResponseToRequestIssuer($receivedRequest, $sentResponse);
            return;
        }
    }
}