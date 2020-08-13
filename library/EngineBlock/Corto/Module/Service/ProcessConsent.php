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

use OpenConext\EngineBlock\Service\AuthenticationStateHelperInterface;
use OpenConext\EngineBlock\Service\ProcessingStateHelperInterface;
use SAML2\Constants;
use SAML2\Response;
use Symfony\Component\HttpFoundation\Request;

class EngineBlock_Corto_Module_Service_ProcessConsent
    implements EngineBlock_Corto_Module_Service_ServiceInterface
{
    /**
     * @var \EngineBlock_Corto_ProxyServer
     */
    protected $_server;

    /**
     * @var \EngineBlock_Corto_XmlToArray
     */
    protected $_xmlConverter;

    /**
     * @var EngineBlock_Corto_Model_Consent_Factory
     */
    private $_consentFactory;

    /**
     * @var AuthenticationStateHelperInterface
     */
    private $_authenticationStateHelper;

    /**
     * @var ProcessingStateHelperInterface
     */
    private $_processingStateHelper;

    /**
     * @param EngineBlock_Corto_ProxyServer $server
     * @param EngineBlock_Corto_XmlToArray $xmlConverter
     * @param EngineBlock_Corto_Model_Consent_Factory $consentFactory
     * @param AuthenticationStateHelperInterface $stateHelper
     * @param ProcessingStateHelperInterface $processingStateHelper
     */
    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        EngineBlock_Corto_XmlToArray $xmlConverter,
        EngineBlock_Corto_Model_Consent_Factory $consentFactory,
        AuthenticationStateHelperInterface $stateHelper,
        ProcessingStateHelperInterface $processingStateHelper
    ) {
        $this->_server = $server;
        $this->_xmlConverter = $xmlConverter;
        $this->_consentFactory = $consentFactory;
        $this->_authenticationStateHelper = $stateHelper;
        $this->_processingStateHelper = $processingStateHelper;
    }

    /**
     * @param $serviceName
     * @param Request $httpRequest
     */
    public function serve($serviceName, Request $httpRequest)
    {
        $processStep = $this->_processingStateHelper->getStepByRequestId($httpRequest->get('ID'), ProcessingStateHelperInterface::STEP_CONSENT);

        /** @var Response|EngineBlock_Saml2_ResponseAnnotationDecorator $response */
        $response = $processStep->getResponse();

        $request = $this->_server->getReceivedRequestFromResponse($response);
        $serviceProvider = $this->_server->getRepository()->fetchServiceProviderByEntityId($request->getIssuer()->getValue());

        $destinationMetadata = EngineBlock_SamlHelper::getDestinationSpMetadata(
            $serviceProvider,
            $request,
            $this->_server->getRepository()
        );

        if ($httpRequest->get('consent', 'no') !== 'yes') {
            throw new EngineBlock_Corto_Exception_NoConsentProvided('No consent given...');
        }

        $attributes = $response->getAssertion()->getAttributes();
        $consentRepository = $this->_consentFactory->create($this->_server, $response, $attributes);

        if (!$consentRepository->explicitConsentWasGivenFor($serviceProvider)) {
            $consentRepository->giveExplicitConsentFor($destinationMetadata);
        }

        $response->setConsent(Constants::CONSENT_OBTAINED);
        $response->setDestination($response->getReturn());
        $response->setDeliverByBinding('INTERNAL');

        // Finally, mark the authentication procedure as being complete.
        $authenticationState = $this->_authenticationStateHelper->getAuthenticationState();
        $authenticationState->completeCurrentProcedure($response->getInResponseTo());

        $this->_server->getBindingsModule()->send(
            $response,
            $serviceProvider
        );
    }
}
