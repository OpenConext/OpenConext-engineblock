<?php

/**
 * Copyright 2025 SURFnet B.V.
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

use OpenConext\EngineBlock\Service\AuthenticationStateHelperInterface;
use OpenConext\EngineBlock\Service\ProcessingStateHelperInterface;
use OpenConext\EngineBlock\Stepup\StepupGatewayCallOutHelper;
use OpenConext\EngineBlockBundle\Sbs\Dto\AttributesRequest;
use OpenConext\EngineBlockBundle\Sbs\SbsAttributeMerger;
use Symfony\Component\HttpFoundation\Request;

class EngineBlock_Corto_Module_Service_SRAMInterrupt
    implements EngineBlock_Corto_Module_Service_ServiceInterface
{
    /**
     * @var EngineBlock_Corto_ProxyServer
     */
    protected $_server;

    /**
     * @var AuthenticationStateHelperInterface
     */
    private $_authenticationStateHelper;

    /**
     * @var ProcessingStateHelperInterface
     */
    private $_processingStateHelper;

    /**
     * @var StepupGatewayCallOutHelper
     */
    private $_stepupGatewayCallOutHelper;

    /**
     * @var SbsAttributeMerger
     */
    private $_sbsAttributeMerger;


    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        AuthenticationStateHelperInterface $stateHelper,
        ProcessingStateHelperInterface $processingStateHelper,
        StepupGatewayCallOutHelper $stepupGatewayCallOutHelper,
        SbsAttributeMerger $sbsAttributeMerger
    )
    {
        $this->_server = $server;
        $this->_authenticationStateHelper = $stateHelper;
        $this->_processingStateHelper = $processingStateHelper;
        $this->_stepupGatewayCallOutHelper = $stepupGatewayCallOutHelper;
        $this->_sbsAttributeMerger = $sbsAttributeMerger;
    }

    /**
     * route that receives the user when they get back from their SBS interrupt,
     * fetches the attributes from SBS,
     * and resumes the AuthN flow.
     *
     * @param $serviceName
     * @param Request $httpRequest
     */
    public function serve($serviceName, Request $httpRequest)
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();

        // Get active request
        $id = $httpRequest->get('ID');

        $nextProcessStep = $this->_processingStateHelper->getStepByRequestId(
            $id,
            ProcessingStateHelperInterface::STEP_SRAM
        );

        $receivedResponse = $nextProcessStep->getResponse();
        $receivedRequest = $this->_server->getReceivedRequestFromResponse($receivedResponse);

        $attributes = $receivedResponse->getAssertion()->getAttributes();
        $nonce = $receivedResponse->getSRAMInterruptNonce();

        $request = AttributesRequest::create($nonce);
        $interruptResponse = $this->getSbsClient()->requestAttributesFor($request);

        if (!empty($interruptResponse->attributes)) {
            $attributes = $this->_sbsAttributeMerger->mergeAttributes($attributes, $interruptResponse->attributes);
            $receivedResponse->getAssertion()->setAttributes($attributes);
        }

        /*
         * Continue to Consent/StepUp
         */

        if ($this->_server->handleConsentAuthenticationCallout($receivedResponse, $receivedRequest)) {
            return;
        }

        $this->_server->handleStepupAuthenticationCallout($receivedResponse, $receivedRequest);
    }

    private function getSbsClient()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getSbsClient();
    }
}
