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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures;

use OpenConext\EngineBlockBundle\Authentication\AuthenticationLoopGuard;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationLoopGuardInterface;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationProcedureMap;
use OpenConext\EngineBlockBundle\Exception\AuthenticationSessionLimitExceededException;
use OpenConext\EngineBlockBundle\Exception\StuckInAuthenticationLoopException;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore\AbstractDataStore;
use OpenConext\Value\Saml\Entity;

final class FunctionalTestingAuthenticationLoopGuard implements AuthenticationLoopGuardInterface
{
    /**
     * @var AuthenticationLoopGuardInterface
     */
    private $originalAuthenticationLoopGuard;

    /**
     * @var AuthenticationLoopGuardInterface
     */
    private $activeAuthenticationLoopGuard;

    /**
     * @var AbstractDataStore
     */
    private $dataStore;

    /**
     * @var false|array
     */
    private $authenticationGuardFixture;


    public function __construct(
        AuthenticationLoopGuardInterface $authenticationLoopGuard,
        AbstractDataStore $dataStore
    ) {
        $this->originalAuthenticationLoopGuard = $authenticationLoopGuard;
        $this->dataStore               = $dataStore;

        $this->authenticationGuardFixture = $dataStore->load(false);
    }

    /**
     * @param int $maximumAuthenticationProceduresAllowed
     * @param int $timeFrameForAuthenticationLoopInSeconds
     * @param int $maximumAuthenticationsPerSession
     */
    public function saveAuthenticationLoopGuardConfiguration(
        int $maximumAuthenticationProceduresAllowed,
        int $timeFrameForAuthenticationLoopInSeconds,
        int $maximumAuthenticationsPerSession
    ) {
        $this->authenticationGuardFixture['maximumAuthenticationProceduresAllowed'] = $maximumAuthenticationProceduresAllowed;
        $this->authenticationGuardFixture['timeFrameForAuthenticationLoopInSeconds'] = $timeFrameForAuthenticationLoopInSeconds;
        $this->authenticationGuardFixture['maximumAuthenticationsPerSession'] = $maximumAuthenticationsPerSession;

        $this->dataStore->save($this->authenticationGuardFixture);
    }

    /**
     * @param Entity $serviceProvider
     * @param AuthenticationProcedureMap $pastAuthenticationProcedures
     */
    public function detectsAuthenticationLoop(
        Entity $serviceProvider,
        AuthenticationProcedureMap $pastAuthenticationProcedures
    ): bool {
        $this->initAuthenticationLoopGuard();

        if ($this->activeAuthenticationLoopGuard->detectsAuthenticationLoop($serviceProvider, $pastAuthenticationProcedures)) {
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

        return false;
    }

    public function detectsAuthenticationLimit(AuthenticationProcedureMap $pastAuthenticationProcedures): bool
    {
        $this->initAuthenticationLoopGuard();

        if ($this->activeAuthenticationLoopGuard->detectsAuthenticationLimit($pastAuthenticationProcedures)) {
            throw new AuthenticationSessionLimitExceededException(
                'More than the configured maximum authentication procedures for the current user'
                . ' the user seems to be stuck in an authentication loop. '
                . ' Aborting the current authentication procedure.'
            );
        }

        return false;
    }

    public function cleanUp()
    {
        $this->authenticationGuardFixture = [];
        $this->activeAuthenticationLoopGuard = $this->originalAuthenticationLoopGuard;
        $this->dataStore->save($this->authenticationGuardFixture);
    }

    private function initAuthenticationLoopGuard(): void
    {
        if ($this->authenticationGuardFixture === false) {
            $this->activeAuthenticationLoopGuard = $this->originalAuthenticationLoopGuard;
        } else {
            $this->activeAuthenticationLoopGuard = new AuthenticationLoopGuard(
                $this->authenticationGuardFixture['maximumAuthenticationProceduresAllowed'],
                $this->authenticationGuardFixture['timeFrameForAuthenticationLoopInSeconds'],
                $this->authenticationGuardFixture['maximumAuthenticationsPerSession']
            );
        }
    }
}
