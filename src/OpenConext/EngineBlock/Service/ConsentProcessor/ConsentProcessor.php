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
use EngineBlock_SamlHelper;
use OpenConext\EngineBlock\Service\AuthenticationStateHelperInterface;
use OpenConext\EngineBlock\Service\ConsentFactoryInterface;
use SAML2\Constants;
use SAML2\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

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

    /**
     * @param ConsentProcessorAdapterInterface $adapter
     * @param ConsentFactoryInterface $consentFactory
     * @param AuthenticationStateHelperInterface $stateHelper
     */
    public function __construct(
        ConsentProcessorAdapterInterface $adapter,
        ConsentFactoryInterface $consentFactory,
        AuthenticationStateHelperInterface $stateHelper
    ) {
        $this->proxyServer = $adapter->getProxyServer();
        $this->consentFactory = $consentFactory;
        $this->authenticationStateHelper = $stateHelper;
    }

    public function serve()
    {
        if (!isset($_SESSION['consent'])) {
            throw new EngineBlock_Corto_Module_Services_SessionLostException('Session lost after consent');
        }
        if (!isset($_SESSION['consent'][$_POST['ID']]['response'])) {
            throw new EngineBlock_Corto_Module_Services_SessionLostException(
                sprintf('Stored response for ResponseID "%s" not found', $_POST['ID'])
            );
        }
        /** @var Response|EngineBlock_Saml2_ResponseAnnotationDecorator $response */
        $response = $_SESSION['consent'][$_POST['ID']]['response'];

        $request = $this->proxyServer->getReceivedRequestFromResponse($response);
        $serviceProvider = $this->proxyServer->getRepository()->fetchServiceProviderByEntityId($request->getIssuer());

        $destinationMetadata = EngineBlock_SamlHelper::getDestinationSpMetadata(
            $serviceProvider,
            $request,
            $this->proxyServer->getRepository()
        );

        if (!isset($_POST['consent']) || $_POST['consent'] !== 'yes') {
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
}
