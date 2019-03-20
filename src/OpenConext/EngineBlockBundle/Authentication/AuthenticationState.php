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

namespace OpenConext\EngineBlockBundle\Authentication;

use DateTimeImmutable;
use OpenConext\EngineBlockBundle\Exception\LogicException;
use OpenConext\EngineBlockBundle\Exception\StuckInAuthenticationLoopException;
use OpenConext\Value\Saml\Entity;

final class AuthenticationState implements AuthenticationStateInterface
{
    /**
     * @var AuthenticationProcedure
     */
    private $currentAuthenticationProcedure;

    /**
     * @var AuthenticationProcedureList
     */
    private $authenticationProcedures;

    /**
     * @var AuthenticationLoopGuardInterface
     */
    private $authenticationLoopGuard;

    public function __construct(AuthenticationLoopGuardInterface $authenticationLoopGuard)
    {
        $this->authenticationProcedures = new AuthenticationProcedureList;
        $this->authenticationLoopGuard  = $authenticationLoopGuard;
    }

    /**
     * @param Entity $serviceProvider
     * @return void
     */
    public function startAuthenticationOnBehalfOf(Entity $serviceProvider)
    {
        $this->currentAuthenticationProcedure = AuthenticationProcedure::onBehalfOf($serviceProvider);

        $inAuthenticationLoop = $this->authenticationLoopGuard->detectsAuthenticationLoop(
            $serviceProvider,
            $this->authenticationProcedures
        );

        if ($inAuthenticationLoop) {
            throw new StuckInAuthenticationLoopException(
                sprintf(
                    'More than the configured maximum authentication procedures for the current user from SP "%s"'
                    . ' occurred within the configured amount of seconds,'
                    . ' the user seems to be stuck in an authentication loop. '
                    . ' Aborting the current authentication procedure.',
                    $serviceProvider->getEntityId()
                )
            );
        }

        $this->authenticationProcedures = $this->authenticationProcedures->add($this->currentAuthenticationProcedure);
    }

    /**
     * @param Entity $identityProvider
     * @return void
     */
    public function authenticatedAt(Entity $identityProvider)
    {
        if ($this->currentAuthenticationProcedure === null) {
            throw new LogicException(
                'Current authentication procedure cannot be authenticated:'
                 . ' authentication procedure has not been started'
            );
        }

        $this->currentAuthenticationProcedure->authenticatedAt($identityProvider);
    }

    /**
     * @return void
     */
    public function completeCurrentProcedure()
    {
        if ($this->currentAuthenticationProcedure === null) {
            throw new LogicException(
                'Current authentication procedure cannot be completed:'
                . ' authentication procedure has not been started'
            );
        }

        if ($this->currentAuthenticationProcedure->hasBeenAuthenticated()) {
            throw new LogicException(
                'Current authentication procedure cannot be completed:'
                . ' authentication procedure has not been authenticated'
            );
        }

        $this->currentAuthenticationProcedure->completeOn(new DateTimeImmutable);

        $this->currentAuthenticationProcedure = null;
    }
}
