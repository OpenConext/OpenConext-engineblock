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

namespace OpenConext\EngineBlockBundle\Authentication;

use Assert\AssertionFailedException;
use DateTimeImmutable;
use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlockBundle\Exception\AuthenticationSessionLimitExceededException;
use OpenConext\EngineBlockBundle\Exception\LogicException;
use OpenConext\EngineBlockBundle\Exception\StuckInAuthenticationLoopException;
use OpenConext\Value\Saml\Entity;

final class AuthenticationState implements AuthenticationStateInterface
{
    /**
     * @var AuthenticationProcedureMap
     */
    private $authenticationProcedures;

    /**
     * @var AuthenticationLoopGuardInterface
     */
    private $authenticationLoopGuard;

    public function __construct(AuthenticationLoopGuardInterface $authenticationLoopGuard)
    {
        $this->authenticationProcedures = new AuthenticationProcedureMap;
        $this->authenticationLoopGuard = $authenticationLoopGuard;
    }

    /**
     * @param string $requestId
     * @param Entity $serviceProvider
     * @return void
     * @throws AssertionFailedException
     */
    public function startAuthenticationOnBehalfOf(string $requestId, Entity $serviceProvider): void
    {
        Assertion::string($requestId, 'The requestId must be a string (XML ID) value');
        $currentAuthenticationProcedure = AuthenticationProcedure::onBehalfOf($serviceProvider);

        // Validate if the processed authentications this session do not exceed the configured maximum of authentications
        $authenticationLimitExceeded = $this->authenticationLoopGuard->detectsAuthenticationLimit(
            $this->authenticationProcedures
        );

        if ($authenticationLimitExceeded) {
            session_destroy();

            throw new AuthenticationSessionLimitExceededException(
                'More than the configured maximum authentication procedures for this session'
                    . ' the user seems to have started too much authentications this session. '
                    . ' Resetting the session.'
            );
        }

        // Validate if the processed authentications for the service provider for this session do not exceed
        // the configured maximum authentications in a configured time frame.
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

        $this->authenticationProcedures = $this->authenticationProcedures->add(
            $requestId,
            $currentAuthenticationProcedure
        );
    }

    /**
     * Validates if an request can be found in session and sets the Identity Provider in the authentication Procedure
     *
     * @param string $requestId
     * @param Entity $identityProvider
     * @return void
     * @throws AssertionFailedException
     */
    public function authenticatedAt(string $requestId, Entity $identityProvider): void
    {
        Assertion::string($requestId, 'The requestId must be a string (XML ID) value');

        $currentRequest = $this->authenticationProcedures->find($requestId);

        if ($currentRequest === null) {
            throw new LogicException(
                sprintf(
                    'The requested authentication procedure with requestId "%s" couldn\'t be found in the ' .
                    'session storage.',
                    $requestId
                )
            );
        }

        $currentRequest->authenticatedAt($identityProvider);
    }

    /**
     * Completes the authentication procedure and sets the completion time
     *
     * @param string $requestId
     * @return void
     * @throws AssertionFailedException
     */
    public function completeCurrentProcedure(string $requestId): void
    {
        Assertion::string($requestId, 'The requestId must be a string (XML ID) value');
        $currentRequest = $this->authenticationProcedures->find($requestId);
        if ($currentRequest === null) {
            throw new LogicException(
                sprintf(
                    'The requested authentication procedure with requestId "%s" couldn\'t be found in the ' .
                    'session storage in order to complete.',
                    $requestId
                )
            );
        }

        if (!$currentRequest->hasBeenAuthenticated()) {
            throw new LogicException(
                sprintf(
                    'The requested authentication procedure with requestId "%s" has not been authenticated.',
                    $requestId
                )
            );
        }

        $currentRequest->completeOn(new DateTimeImmutable());
    }

    /**
     * Validates if the current session contains at least one authentication procedure that has been
     * authenticated and completed
     *
     * @return bool
     * @throws LogicException
     */
    public function isAuthenticated(): bool
    {
        if ($this->authenticationProcedures === null) {
            throw new LogicException('The requested authentication procedure couldn\'t be found in the ' .
                'session storage in order to complete.');
        }
        return $this->authenticationProcedures->hasBeenAuthenticated();
    }
}
