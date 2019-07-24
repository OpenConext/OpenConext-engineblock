<?php
/**
 * Copyright 2019 SURFnet B.V.
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

use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Service\ProcessingStateHelperInterface;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationState;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class EngineBlock_Corto_Module_Service_SfoAssertionConsumer implements EngineBlock_Corto_Module_Service_ServiceInterface
{
    /**
     * @var EngineBlock_Corto_ProxyServer
     */
    private $_server;

    /**
     * @var ProcessingStateHelperInterface
     */
    private $_processingStateHelper;

    /**
     * @var Session
     */
    private $_session;

    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        Session $session,
        ProcessingStateHelperInterface $processingStateHelper
    ) {
        $this->_server = $server;
        $this->_session = $session;
        $this->_processingStateHelper = $processingStateHelper;
    }

    /**
     * @param $serviceName
     * @param Request $httpRequest
     */
    public function serve($serviceName, Request $httpRequest)
    {
        $serviceEntityId = $this->_server->getUrl('sfoMetadataService');
        $expectedDestination = $this->_server->getUrl('sfoAssertionConsumerService');
        $receivedResponse = $this->_server->getBindingsModule()->receiveResponse($serviceEntityId, $expectedDestination);
        $receivedRequest = $this->_server->getReceivedRequestFromResponse($receivedResponse);

        $application = EngineBlock_ApplicationSingleton::getInstance();
        $log = $application->getLogInstance();

        $this->_server->checkResponseSignatureMethods($receivedResponse);

        $sp  = $this->_server->getRepository()->fetchServiceProviderByEntityId($receivedRequest->getIssuer());

        // Verify the SP requester chain.
        EngineBlock_SamlHelper::getSpRequesterChain(
            $sp,
            $receivedRequest,
            $this->_server->getRepository()
        );

        // Get active request
        $processStep = $this->_processingStateHelper->getStepByRequestId($receivedRequest->getId(), ProcessingStateHelperInterface::STEP_SFO);


        // TODO validate return status

        ///////
        $receivedResponse = $processStep->getResponse();

        $nextProcessStep = $this->_processingStateHelper->getStepByRequestId(
            $receivedRequest->getId(),
            ProcessingStateHelperInterface::STEP_CONSENT
        );

        $this->_server->sendConsentAuthenticationRequest($receivedResponse, $receivedRequest, $nextProcessStep->getRole(), $this->getAuthenticationState());

        return;
    }

    /**
     * @return AuthenticationState
     */
    private function getAuthenticationState()
    {
        return $this->_session->get('authentication_state');
    }
}
