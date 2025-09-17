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
use Countable;
use DateTimeInterface;
use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Saml\Entity;

final class AuthenticationProcedureMap implements Countable
{
    /**
     * The AuthenticationProcedure mapped by their corresponding request id
     * @var AuthenticationProcedure[]
     */
    private $authenticationProcedures;

    public function __construct(array $authenticationProcedures = [])
    {
        Assertion::allIsInstanceOf($authenticationProcedures, AuthenticationProcedure::class);
        $this->authenticationProcedures = $authenticationProcedures;
    }

    /**
     * @param $requestId
     * @param AuthenticationProcedure $authenticationProcedure
     * @return AuthenticationProcedureMap
     */
    public function add($requestId, AuthenticationProcedure $authenticationProcedure): AuthenticationProcedureMap
    {
        $newAuthenticationProcedures = $this->authenticationProcedures;
        $newAuthenticationProcedures[$requestId] = $authenticationProcedure;

        return new self($newAuthenticationProcedures);
    }

    /**
     * @param string $requestId
     * @return AuthenticationProcedure|null
     * @throws AssertionFailedException
     */
    public function find($requestId): ?AuthenticationProcedure
    {
        Assertion::string($requestId, 'The requestId must be a string (XML ID) value');
        if (isset($this->authenticationProcedures[$requestId])) {
            return $this->authenticationProcedures[$requestId];
        }
        return null;
    }

    /**
     * @param Entity $entity
     * @return AuthenticationProcedureMap
     */
    public function filterOnBehalfOf(Entity $entity): AuthenticationProcedureMap
    {
        $filterMethod = function (AuthenticationProcedure $authenticationProcedure) use ($entity) {
            return $authenticationProcedure->isOnBehalfOf($entity);
        };

        return new self(array_filter($this->authenticationProcedures, $filterMethod));
    }

    /**
     * @param DateTimeInterface $startDate
     * @return AuthenticationProcedureMap
     */
    public function filterProceduresCompletedAfter(DateTimeInterface $startDate): AuthenticationProcedureMap
    {
        $filterMethod = function (AuthenticationProcedure $authenticationProcedure) use ($startDate) {
            return $authenticationProcedure->isCompletedAfter($startDate);
        };

        return new self(array_filter($this->authenticationProcedures, $filterMethod));
    }

    /**
     * @param AuthenticationProcedure $other
     * @return bool
     */
    public function contains(AuthenticationProcedure $other): bool
    {
        foreach ($this->authenticationProcedures as $authenticationProcedure) {
            if ($authenticationProcedure->equals($other)) {
                return true;
            }
        }

        return false;
    }

    public function count(): int
    {
        return count($this->authenticationProcedures);
    }

    /**
     * Validates if any of the authenticationProcedures has been authenticated and completed
     *
     * @return bool
     */
    public function hasBeenAuthenticated(): bool
    {
        foreach ($this->authenticationProcedures as $authenticationProcedure) {
            if ($authenticationProcedure->hasBeenAuthenticated() && $authenticationProcedure->isCompleted()) {
                return true;
            }
        }
        return false;
    }
}
