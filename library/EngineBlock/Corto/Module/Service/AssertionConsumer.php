<?php

class EngineBlock_Corto_Module_Service_AssertionConsumer extends EngineBlock_Corto_Module_Service_Abstract
{
    public function serve($serviceName)
    {
        $receivedResponse = $this-> _server->getBindingsModule()->receiveResponse();
        $receivedRequest = $this->_server->getReceivedRequestFromResponse(
            $receivedResponse->getInResponseTo()
        );

        // Flush log if SP or IdP has additional logging enabled
        $sp = $this->_server->getRemoteEntity($receivedRequest->getIssuer());
        $idp = $this->_server->getRemoteEntity($receivedResponse->getIssuer());
        if (
            $this->_server->getConfig('debug', false) ||
            EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging($sp, $idp)
        ) {
            EngineBlock_ApplicationSingleton::getInstance()->getLogInstance()->flushQueue();
        }

        if ($receivedRequest->isDebugRequest()) {
            $_SESSION['debugIdpResponse'] = $receivedResponse;
            $this->_server->redirect($this->_server->getUrl('debugSingleSignOnService'), 'Show original Response from IDP');
            return;
        }

        // Cache the response
        EngineBlock_Corto_Model_Response_Cache::cacheResponse(
            $receivedRequest,
            $receivedResponse,
            EngineBlock_Corto_Model_Response_Cache::RESPONSE_CACHE_TYPE_IN
        );

        $this->_server->filterInputAssertionAttributes($receivedResponse, $receivedRequest);

        $processingEntities = $this->_getReceivedResponseProcessingEntities($receivedRequest);
        if (!empty($processingEntities)) {
            $firstProcessingEntity = array_shift($processingEntities);
            $_SESSION['Processing'][$receivedRequest->getId()]['RemainingEntities']   = $processingEntities;
            $_SESSION['Processing'][$receivedRequest->getId()]['OriginalDestination'] = $receivedResponse->getDestination();
            $_SESSION['Processing'][$receivedRequest->getId()]['OriginalIssuer']      = $receivedResponse->getOriginalIssuer();
            $_SESSION['Processing'][$receivedRequest->getId()]['OriginalBinding']     = $receivedResponse->getOriginalBinding();

            $this->_server->setProcessingMode();
            $newResponse = $this->_server->createEnhancedResponse($receivedRequest, $receivedResponse);

            // Change the destiny of the received response
            $newResponse->setInResponseTo($receivedResponse->getInResponseTo());
            $newResponse->setDestination($firstProcessingEntity['Location']);
            $newResponse->setDeliverByBinding($firstProcessingEntity['Binding']);
            $newResponse->setReturn($this->_server->getUrl('processedAssertionConsumerService'));

            $attributes = $newResponse->getAssertion()->getAttributes();
            $attributes['urn:org:openconext:corto:internal:sp-entity-id'] = array($receivedRequest->getIssuer());
            $newResponse->getAssertion()->setAttributes($attributes);

            $this->_server->getBindingsModule()->send($newResponse, $firstProcessingEntity);
        }
        else {
            // Cache the response
            EngineBlock_Corto_Model_Response_Cache::cacheResponse(
                $receivedRequest,
                $receivedResponse,
                EngineBlock_Corto_Model_Response_Cache::RESPONSE_CACHE_TYPE_OUT
            );

            $newResponse = $this->_server->createEnhancedResponse($receivedRequest, $receivedResponse);
            $this->_server->sendResponseToRequestIssuer($receivedRequest, $newResponse);
        }
    }

    protected function _getReceivedResponseProcessingEntities(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $receivedRequest
    ) {
        $currentEntityProcessing = $this->_server->getConfig('Processing', array());

        $remoteEntity = $this->_server->getRemoteEntity($receivedRequest->getIssuer());

        $processing = $currentEntityProcessing;
        if (isset($remoteEntity['Processing'])) {
            $processing += $remoteEntity['Processing'];
        }

        return $processing;
    }
}
