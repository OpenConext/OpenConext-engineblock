<?php

class EngineBlock_Corto_Module_Service_AssertionConsumer extends EngineBlock_Corto_Module_Service_Abstract
{
    public function serve($serviceName)
    {
        $receivedResponse = $this-> _server->getBindingsModule()->receiveResponse();
        $receivedRequest = $this->_server->getReceivedRequestFromResponse(
            $receivedResponse[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'InResponseTo']
        );

        // Flush log if SP or IdP has additional logging enabled
        $sp = $this->_server->getRemoteEntity(EngineBlock_SamlHelper::extractIssuerFromMessage($receivedRequest));
        $idp = $this->_server->getRemoteEntity(EngineBlock_SamlHelper::extractIssuerFromMessage($receivedResponse));
        if (EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging($sp, $idp)) {
            EngineBlock_ApplicationSingleton::getInstance()->getLogInstance()->flushQueue();
        }

        $isDebugRequest = (isset($receivedRequest[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Debug']) &&
            $receivedRequest[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Debug']);
        if ($isDebugRequest) {
            $_SESSION['debugIdpResponse'] = $receivedResponse;
            $this->_server->redirect($this->_server->getUrl('debugSingleSignOnService'), 'Show original Response from IDP');
            return;
        }

        // Cache the response
        EngineBlock_Corto_Model_Response_Cache::cacheResponse(
            $receivedRequest,
            $receivedResponse,
            EngineBlock_Corto_Model_Response_Cache::RESPONSE_CACHE_TYPE_IN,
            $this->_server->getVirtualOrganisationContext()
        );

        $this->_server->filterInputAssertionAttributes($receivedResponse, $receivedRequest);

        $processingEntities = $this->_getReceivedResponseProcessingEntities($receivedRequest, $receivedResponse);
        if (!empty($processingEntities)) {
            $firstProcessingEntity = array_shift($processingEntities);
            $_SESSION['Processing'][$receivedRequest[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['RemainingEntities']   = $processingEntities;
            $_SESSION['Processing'][$receivedRequest[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['OriginalDestination'] = $receivedResponse[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Destination'];
            $_SESSION['Processing'][$receivedRequest[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['OriginalIssuer']      = $receivedResponse['saml:Assertion']['saml:Issuer'][EngineBlock_Corto_XmlToArray::VALUE_PFX];
            $_SESSION['Processing'][$receivedRequest[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID']]['OriginalBinding']     = $receivedResponse[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['ProtocolBinding'];

            $this->_server->setProcessingMode();
            $newResponse = $this->_server->createEnhancedResponse($receivedRequest, $receivedResponse);

            // Change the destiny of the received response
            $newResponse[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'InResponseTo']  = $receivedResponse[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'InResponseTo'];
            $newResponse[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Destination']   = $firstProcessingEntity['Location'];
            $newResponse[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['ProtocolBinding']  = $firstProcessingEntity['Binding'];
            $newResponse[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Return']           = $this->_server->getUrl('processedAssertionConsumerService');
            $newResponse[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['paramname']        = 'SAMLResponse';

            $responseAssertionAttributes = &$newResponse['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute'];
            $attributes = $this->_xmlConverter->attributesToArray($responseAssertionAttributes);
            $attributes['urn:org:openconext:corto:internal:sp-entity-id'] = array($receivedRequest['saml:Issuer'][EngineBlock_Corto_XmlToArray::VALUE_PFX]);
            $responseAssertionAttributes = EngineBlock_Corto_XmlToArray::array2attributes($attributes);

            $this->_server->getBindingsModule()->send($newResponse, $firstProcessingEntity);
        }
        else {
            // Cache the response
            EngineBlock_Corto_Model_Response_Cache::cacheResponse(
                $receivedRequest,
                $receivedResponse,
                EngineBlock_Corto_Model_Response_Cache::RESPONSE_CACHE_TYPE_OUT,
                $this->_server->getVirtualOrganisationContext()
            );

            $newResponse = $this->_server->createEnhancedResponse($receivedRequest, $receivedResponse);
            $this->_server->sendResponseToRequestIssuer($receivedRequest, $newResponse);
        }
    }

    protected function _getReceivedResponseProcessingEntities(array $receivedRequest, array $receivedResponse)
    {
        $currentEntityProcessing = $this->_server->getConfig('Processing', array());

        $remoteEntity = $this->_server->getRemoteEntity($receivedRequest['saml:Issuer'][EngineBlock_Corto_XmlToArray::VALUE_PFX]);

        $processing = $currentEntityProcessing;
        if (isset($remoteEntity['Processing'])) {
            $processing += $remoteEntity['Processing'];
        }

        return $processing;
    }
}
