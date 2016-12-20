<?php

/**
 * Copyright 2016 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\EventListener;

use EngineBlock_ApplicationSingleton;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationState;
use OpenConext\EngineBlockBundle\Controller\AuthenticationLoopThrottlingController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

final class AuthenticationStateInitializer
{
    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (!$event->getController()[0] instanceof AuthenticationLoopThrottlingController) {
            return;
        }

        $authenticationState = $this->session->get('authentication_state');
        if ($authenticationState === null) {
            $authenticationLoopGuard = $this->getAuthenticationLoopGuard();

            $this->session->set('authentication_state', new AuthenticationState($authenticationLoopGuard));
        }
    }

    public function getAuthenticationLoopGuard()
    {
        // This allows us to overwrite the authentication loop guard when running functional tests
        return EngineBlock_ApplicationSingleton::getInstance()
            ->getDiContainer()
            ->getAuthenticationLoopGuard();
    }
}
