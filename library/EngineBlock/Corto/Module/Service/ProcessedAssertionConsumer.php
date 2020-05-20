<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use OpenConext\EngineBlock\Service\ProcessingStateHelperInterface;
use Symfony\Component\HttpFoundation\Request;

class EngineBlock_Corto_Module_Service_ProcessedAssertionConsumer implements EngineBlock_Corto_Module_Service_ServiceInterface
{
    /**
     * @var EngineBlock_Corto_ProxyServer
     */
    private $_server;

    /**
     * @var ProcessingStateHelperInterface
     */
    private $_processingStateHelper;

    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        ProcessingStateHelperInterface $processingStateHelper
    ) {
        $this->_server = $server;
        $this->_processingStateHelper = $processingStateHelper;
    }

    /**
     * @param $serviceName
     * @param Request $httpRequest
     */
    public function serve($serviceName, Request $httpRequest)
    {
        $serviceEntityId = $this->_server->getUrl('spMetadataService');
        $expectedDestination = $this->_server->getUrl('assertionConsumerService');

        $response = $this->_server->getBindingsModule()->receiveResponse($serviceEntityId, $expectedDestination);
        $receivedRequest = $this->_server->getReceivedRequestFromResponse($response);

        if ($receivedRequest->getKeyId()) {
            $this->_server->setKeyId($receivedRequest->getKeyId());
        }

        $this->_processingStateHelper->clearStepByRequestId($receivedRequest->getId());

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

         // Done processing! Send off to SP
        $this->_server->unsetProcessingMode();

        $sentResponse = $this->_server->createEnhancedResponse($receivedRequest, $response);
        $this->_server->sendResponseToRequestIssuer($receivedRequest, $sentResponse);
        return;
    }
}
