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

use OpenConext\EngineBlock\Service\ProcessingStateHelperInterface;
use OpenConext\EngineBlockBundle\Sbs\Dto\AttributesRequest;
use OpenConext\EngineBlockBundle\Sbs\SbsAttributeMerger;
use Symfony\Component\HttpFoundation\Request;

class EngineBlock_Corto_Module_Service_SramInterrupt
    implements EngineBlock_Corto_Module_Service_ServiceInterface
{
    private EngineBlock_Corto_ProxyServer $_server;

    private ProcessingStateHelperInterface $_processingStateHelper;

    private SbsAttributeMerger $_sbsAttributeMerger;


    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        ProcessingStateHelperInterface $processingStateHelper,
        SbsAttributeMerger $sbsAttributeMerger,
    )
    {
        $this->_server = $server;
        $this->_processingStateHelper = $processingStateHelper;
        $this->_sbsAttributeMerger = $sbsAttributeMerger;
    }

    /**
     * route that receives the user when they get back from their SBS interrupt,
     * and resumes the AuthN flow.
     */
    public function serve($serviceName, Request $httpRequest): void
    {
        $id = $httpRequest->get('ID');

        $nextProcessStep = $this->_processingStateHelper->getStepByRequestId(
            $id,
            ProcessingStateHelperInterface::STEP_SRAM
        );

        $receivedResponse = $nextProcessStep->getResponse();
        $receivedRequest = $this->_server->getReceivedRequestFromResponse($receivedResponse);
        $originalAssertion = clone $receivedResponse->getAssertions()[0];

        if(!$receivedRequest instanceof EngineBlock_Saml2_AuthnRequestAnnotationDecorator){
            throw new RuntimeException('Request cannot be empty at this stage');
        }

        $attributes = $receivedResponse->getAssertion()->getAttributes();
        $nonce = $receivedResponse->getSramInterruptNonce();

        $request = new AttributesRequest($nonce);
        $interruptResponse = $this->getSbsClient()->requestAttributesFor($request);

        if (!empty($interruptResponse->attributes)) {
            $attributes = $this->_sbsAttributeMerger->mergeAttributes($attributes, $interruptResponse->attributes);
            $receivedResponse->getAssertion()->setAttributes($attributes);

            // After updating the attributes, reset the types, so SAML2 will set them
            $receivedResponse->getAssertion()->setAttributesValueTypes([]);
        }

        $this->_server->addConsentProcessStep($receivedResponse, $receivedRequest);

        $this->_server->handleConsentAuthenticationCallout($receivedResponse, $receivedRequest);
    }

    private function getSbsClient()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getSbsClient();
    }
}
