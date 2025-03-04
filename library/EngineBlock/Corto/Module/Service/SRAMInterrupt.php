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
use OpenConext\EngineBlockBundle\Sbs\Dto\EntitlementsRequest;
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
    private $sbsAttributeMerger;


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
        $this->sbsAttributeMerger = $sbsAttributeMerger;
    }

    /**
     * route that receives the user when they get back from their SBS interrupt,
     * fetches the attribues from SBS,
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

        $request = EntitlementsRequest::create($nonce);
        $interruptResponse = $this->getSbsClient()->requestEntitlementsFor($request);

        if (!empty($interruptResponse->attributes)) {
            $attributes = $this->sbsAttributeMerger->mergeAttributes($attributes, $interruptResponse->attributes);
            $receivedResponse->getAssertion()->setAttributes($attributes);
        }

        /*
         * Continue to Consent/StepUp
         */

        // Flush log if SP or IdP has additional logging enabled
        $issuer = $receivedResponse->getIssuer() ? $receivedResponse->getIssuer()->getValue() : '';
        $idp = $this->_server->getRepository()->fetchIdentityProviderByEntityId($issuer);

        if ($receivedRequest->isDebugRequest()) {
            $sp = $this->_server->getEngineSpRole($this->_server);
        } else {
            $issuer = $receivedRequest->getIssuer() ? $receivedRequest->getIssuer()->getValue() : '';
            $sp = $this->_server->getRepository()->fetchServiceProviderByEntityId($issuer);
        }

        // When dealing with an SP that acts as a trusted proxy, we should use the proxying SP and not the proxy itself.
        if ($sp->getCoins()->isTrustedProxy()) {
            // Overwrite the trusted proxy SP instance with that of the SP that uses the trusted proxy.
            $sp = $this->_server->findOriginalServiceProvider($receivedRequest, $this->_server->getLogger());
        }

        $pdpLoas = $receivedResponse->getPdpRequestedLoas();
        $loaRepository = $application->getDiContainer()->getLoaRepository();
        $authnRequestLoas = $receivedRequest->getStepupObligations($loaRepository->getStepUpLoas());

        $shouldUseStepup = $this->_stepupGatewayCallOutHelper->shouldUseStepup($idp, $sp, $authnRequestLoas, $pdpLoas);

        // Goto consent if no Stepup authentication is needed
        if (!$shouldUseStepup) {
            $this->_server->sendConsentAuthenticationRequest($receivedResponse, $receivedRequest, $nextProcessStep->getRole(), $this->_authenticationStateHelper->getAuthenticationState());
            return;
        }

        $this->_server->getLogger()->info('Handle Stepup authentication callout');

        // Add Stepup authentication step
        $currentProcessStep = $this->_processingStateHelper->addStep(
            $receivedRequest->getId(),
            ProcessingStateHelperInterface::STEP_STEPUP,
            $application->getDiContainer()->getStepupIdentityProvider($this->_server),
            $receivedResponse
        );

        // Get mapped AuthnClassRef and get NameId
        $nameId = clone $receivedResponse->getNameId();
        $authnClassRef = $this->_stepupGatewayCallOutHelper->getStepupLoa($idp, $sp, $authnRequestLoas, $pdpLoas);

        $this->_server->sendStepupAuthenticationRequest(
            $receivedRequest,
            $currentProcessStep->getRole(),
            $authnClassRef,
            $nameId,
            $sp->getCoins()->isStepupForceAuthn()
        );
    }

    private function getSbsClient()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getSbsClient();
    }
}
