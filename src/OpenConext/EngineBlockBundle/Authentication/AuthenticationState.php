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
        $this->authenticationLoopGuard  = $authenticationLoopGuard;
    }

    /**
     * @param $requestId
     * @param Entity $serviceProvider
     * @return void
     * @throws AssertionFailedException
     */
    public function startAuthenticationOnBehalfOf(string $requestId, Entity $serviceProvider): void
    {
        Assertion::string($requestId, 'The requestId must be a string (XML ID) value');
        $currentAuthenticationProcedure = AuthenticationProcedure::onBehalfOf($serviceProvider);

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
     * Validates if an request can be found in session and if the request is not already authenticated
     *
     * @param string $requestId
     * @return void
     * @throws AssertionFailedException
     * @throws LogicException
     */
    public function validateAuthenticationRequest(string $requestId): void
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

        if ($currentRequest->hasBeenAuthenticated()) {
            throw new LogicException(
                sprintf(
                    'The requested authentication procedure with requestId "%s" has already been authenticated.',
                    $requestId
                )
            );
        }
    }

    /**
     * Completes the authentication procedure and sets the authenticated IDP and time
     *
     * @param string $requestId
     * @param Entity $identityProvider
     * @return void
     * @throws AssertionFailedException
     */
    public function completeCurrentProcedure(string $requestId, Entity $identityProvider): void
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
        if ($currentRequest->hasBeenAuthenticated()) {
            throw new LogicException(
                sprintf(
                    'The requested authentication procedure with requestId "%s" has already been authenticated.',
                    $requestId
                )
            );
        }

        $currentRequest->authenticatedAt($identityProvider);
        $currentRequest->completeOn(new DateTimeImmutable);
    }

    /**
     * Validates if the current session contains a valid authentication
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
