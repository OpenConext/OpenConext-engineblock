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

namespace OpenConext\EngineBlockBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Corto_Adapter;
use OpenConext\EngineBlockBridge\ResponseFactory;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DebugController implements AuthenticationLoopThrottlingController
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        RequestStack $requestStack
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->session = $requestStack->getSession();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function debugSpConnectionAction()
    {
        $proxyServer = new EngineBlock_Corto_Adapter();

        $proxyServer->debugSingleSignOn();

        // Authentication state needs to be registered here as the debug flow differs from the regular flow,
        // yet the procedures for both are completed when consuming the assertion in the ServiceProviderController
        $authenticationState = $this->session->get('authentication_state');

        $requestId = '_00000000-0000-0000-0000-000000000000';
        $authenticationState->startAuthenticationOnBehalfOf(
            $requestId,
            new Entity(new EntityId('debug_sp'), EntityType::SP())
        );

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }
}
