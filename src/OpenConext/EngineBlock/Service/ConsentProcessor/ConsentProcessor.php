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

namespace OpenConext\EngineBlock\Service\ConsentProcessor;

use EngineBlock_Corto_Exception_NoConsentProvided;
use EngineBlock_Corto_Module_Services_SessionLostException;
use EngineBlock_SamlHelper;
use OpenConext\EngineBlock\Message\RequestId;
use OpenConext\EngineBlock\Metadata\ConsentMap;
use OpenConext\EngineBlock\Service\AuthenticationStateHelperInterface;
use OpenConext\EngineBlock\Service\ConsentFactoryInterface;
use SAML2\Constants;
use Symfony\Component\HttpFoundation\RequestStack;

class ConsentProcessor
{
    /**
     * @var ConsentProcessorProxyServerInterface
     */
    protected $proxyServer;

    /**
     * @var ConsentFactoryInterface
     */
    private $consentFactory;

    /**
     * @var AuthenticationStateHelperInterface
     */
    private $authenticationStateHelper;

    public function __construct(
        ConsentFactoryInterface $consentFactory,
        AuthenticationStateHelperInterface $stateHelper,
        RequestStack $request
    ) {
        $this->consentFactory = $consentFactory;
        $this->authenticationStateHelper = $stateHelper;
        $this->request = $request->getCurrentRequest();
        $this->session = $this->request->getSession();
    }


    public function setProxyServer(ConsentProcessorProxyServerInterface $proxyServer)
    {
        $this->proxyServer = $proxyServer;
    }

    public function serve()
    {
        if (is_null($this->proxyServer)) {
            throw new RuntimeException('Before using the service, the current proxy server must be set');
        }
        
        $requestId = $this->getRequestId();

        if (!$this->session->has('consent')) {
            throw new EngineBlock_Corto_Module_Services_SessionLostException('Session lost after consent');
        }
        if (!$this->sessionContainsConsentResponseFor($requestId)) {
            throw new EngineBlock_Corto_Module_Services_SessionLostException(
                sprintf('Stored response for ResponseID "%s" not found', $requestId)
            );
        }

        $response = $this->getConsentResponseFor($requestId)->getResponse();

        $request = $this->proxyServer->getReceivedRequestFromResponse($response);
        $serviceProvider = $this->proxyServer->getRepository()->fetchServiceProviderByEntityId($request->getIssuer());

        $destinationMetadata = EngineBlock_SamlHelper::getDestinationSpMetadata(
            $serviceProvider,
            $request,
            $this->proxyServer->getRepository()
        );

        if (!$this->consentGiven()) {
            throw new EngineBlock_Corto_Exception_NoConsentProvided('No consent given...');
        }

        $attributes = $response->getAssertion()->getAttributes();
        $consentRepository = $this->consentFactory->create($this->proxyServer, $response, $attributes);

        if (!$consentRepository->explicitConsentWasGivenFor($serviceProvider)) {
            $consentRepository->giveExplicitConsentFor($destinationMetadata);
        }

        $response->setConsent(Constants::CONSENT_OBTAINED);
        $response->setDestination($response->getReturn());
        $response->setDeliverByBinding('INTERNAL');

        // Finally, mark the authentication procedure as being complete.
        $authenticationState = $this->authenticationStateHelper->getAuthenticationState();
        $authenticationState->completeCurrentProcedure($response->getInResponseTo());

        $this->proxyServer->getBindingsModule()->send($response, $serviceProvider);
    }

    /**
     * If consent is present in post, test if it was filled with 'yes', in that case consent was given.
     * @return bool
     */
    private function consentGiven()
    {
        return $this->request->request->has('consent') ? $this->request->request->get('consent') === 'yes' : false;
    }

    private function getRequestId()
    {
        return new RequestId($this->request->request->get('ID'));
    }

    private function sessionContainsConsentResponseFor(RequestId $requestId)
    {
        /** @var ConsentMap $consentMap */
        $consentMap = $this->session->get('consent');
        return $consentMap->has($requestId) && !is_null($this->getConsentResponseFor($requestId));
    }

    /**
     * @param RequestId $requestId
     * @return \OpenConext\EngineBlock\Metadata\ConsentInterface|null
     */
    private function getConsentResponseFor(RequestId $requestId)
    {
        /** @var ConsentMap $consentMap */
        $consentMap = $this->session->get('consent');
        return $consentMap->find($requestId);
    }
}
