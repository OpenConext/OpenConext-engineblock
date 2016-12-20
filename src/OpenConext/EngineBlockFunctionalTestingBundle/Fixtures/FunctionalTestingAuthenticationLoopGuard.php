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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures;

use OpenConext\EngineBlockBundle\Authentication\AuthenticationLoopGuard;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationLoopGuardInterface;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationProcedureList;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore\AbstractDataStore;
use OpenConext\Value\Saml\Entity;

final class FunctionalTestingAuthenticationLoopGuard implements AuthenticationLoopGuardInterface
{
    /**
     * @var AuthenticationLoopGuardInterface
     */
    private $authenticationLoopGuard;

    /**
     * @var AbstractDataStore
     */
    private $dataStore;

    /**
     * @var array
     */
    private $authenticationGuardFixture;


    public function __construct(
        AuthenticationLoopGuardInterface $authenticationLoopGuard,
        AbstractDataStore $dataStore
    ) {
        $this->authenticationLoopGuard = $authenticationLoopGuard;
        $this->dataStore               = $dataStore;

        $this->authenticationGuardFixture = $dataStore->load(false);
    }

    /**
     * @param int $maximumAuthenticationCyclesAllowed
     * @param int $timeFrameForAuthenticationLoopInSeconds
     */
    public function saveAuthenticationLoopGuardConfiguration(
        $maximumAuthenticationCyclesAllowed,
        $timeFrameForAuthenticationLoopInSeconds
    ) {
        $this->authenticationGuardFixture['maximumAuthenticationCyclesAllowed'] = $maximumAuthenticationCyclesAllowed;
        $this->authenticationGuardFixture['timeFrameForAuthenticationLoopInSeconds']
            = $timeFrameForAuthenticationLoopInSeconds;

        $this->dataStore->save($this->authenticationGuardFixture);
    }

    /**
     * @param Entity $serviceProvider
     * @param AuthenticationProcedureList $pastAuthenticationProcedures
     */
    public function ensureNotStuckInLoop(
        Entity $serviceProvider,
        AuthenticationProcedureList $pastAuthenticationProcedures
    ) {
        if ($this->authenticationGuardFixture === false) {
            $this->authenticationLoopGuard->ensureNotStuckInLoop($serviceProvider, $pastAuthenticationProcedures);
            return;
        }

        $authenticationLoopGuard = new AuthenticationLoopGuard(
            $this->authenticationGuardFixture['maximumAuthenticationCyclesAllowed'],
            $this->authenticationGuardFixture['timeFrameForAuthenticationLoopInSeconds']
        );

        $authenticationLoopGuard->ensureNotStuckInLoop($serviceProvider, $pastAuthenticationProcedures);
    }

    public function cleanUp()
    {
        $this->authenticationGuardFixture = [];
        $this->dataStore->save($this->authenticationGuardFixture);
    }
}
