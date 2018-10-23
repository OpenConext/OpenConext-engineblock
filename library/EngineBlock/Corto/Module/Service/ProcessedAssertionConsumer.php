<?php

use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;

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

        $wantlogging = true;
        $application = EngineBlock_ApplicationSingleton::getInstance();
        $log = $application->getLogInstance();
        $originalIssuer = $response->getOriginalIssuer();

        if ($originalIssuer) {
            $sp = $this->_server->getRepository()->fetchServiceProviderByEntityId($receivedRequest->getIssuer());
            $idp = $this->_server->getRepository()->fetchIdentityProviderByEntityId($response->getOriginalIssuer());
            $wantlogging = EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging([$sp, $idp]);
        } else {
            $log->warning(
                'The original issuer could not be found in the response. Unable to verify if additional logging is '.
                'required, assuming yes.'
            );
        }

        if ($wantlogging) {
            // Flush log if SP or IdP has additional logging enabled
            $application->flushLog('Activated additional logging for the SP or IdP');
            $log->info('Raw HTTP request', array('http_request' => (string)$application->getHttpRequest()));
        }

        if (!empty($remainingProcessingEntities)) { // Moar processing!
            /** @var AbstractRole $nextProcessingEntity */
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

            $sentResponse = $this->_server->createEnhancedResponse($receivedRequest, $response);
            $this->_server->sendResponseToRequestIssuer($receivedRequest, $sentResponse);
            return;
        }
    }
}
