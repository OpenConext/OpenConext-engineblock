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
use OpenConext\Value\Saml\Entity;

final class AuthenticationState
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

        $this->authenticationLoopGuard->ensureNotStuckInLoop(
            $serviceProvider,
            $this->authenticationProcedures
        );

        $this->authenticationProcedures = $this->authenticationProcedures->add($this->currentAuthenticationProcedure);
    }

    /**
     * @param Entity $identityProvider
     * @return void
     */
    public function authenticateAt(Entity $identityProvider)
    {
        $this->currentAuthenticationProcedure->authenticateAt($identityProvider);
    }

    /**
     * @return void
     */
    public function completeCurrent()
    {
        $this->currentAuthenticationProcedure->completeOn(new DateTimeImmutable);
    }
}
