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

use DateTimeInterface;
use OpenConext\Value\Saml\Entity;

final class AuthenticationProcedure
{
    /**
     * @var Entity|null
     */
    private $identityProvider;

    /**
     * @var DateTimeInterface|null
     */
    private $dateOfCompletion;

    /**
     * @var Entity
     */
    private $serviceProvider;

    private function __construct(Entity $serviceProvider)
    {
        $this->serviceProvider = $serviceProvider;
    }

    /**
     * @param Entity $serviceProvider
     * @return AuthenticationProcedure
     */
    public static function onBehalfOf(Entity $serviceProvider): AuthenticationProcedure
    {
        return new self($serviceProvider);
    }

    /**
     * @param Entity $identityProvider
     */
    public function authenticatedAt(Entity $identityProvider): void
    {
        $this->identityProvider = $identityProvider;
    }

    /**
     * @param DateTimeInterface $dateTime
     */
    public function completeOn(DateTimeInterface $dateTime): void
    {
        $this->dateOfCompletion = $dateTime;
    }

    /**
     * Validates if the authentication has been completed with any Identity Provider
     *
     * @return bool
     */
    public function hasBeenAuthenticated(): bool
    {
        return $this->identityProvider !== null;
    }

    /**
     * Validates if the authentication has been completed with the passed Identity Provider
     *
     * @param Entity $identityProvider
     * @return bool
     */
    public function hasBeenAuthenticatedAt(Entity $identityProvider): bool
    {
        if ($this->identityProvider === null) {
            return false;
        }
        return $this->identityProvider->equals($identityProvider);
    }

    /**
     * @param Entity $serviceProvider
     * @return bool
     */
    public function isOnBehalfOf(Entity $serviceProvider): bool
    {
        return $this->serviceProvider->equals($serviceProvider);
    }

    /**
     * @param DateTimeInterface $date
     * @return bool
     */
    public function isCompletedAfter(DateTimeInterface $date): bool
    {
        if ($this->dateOfCompletion === null) {
            return false;
        }

        return $this->dateOfCompletion > $date;
    }

    /**
     * We assume that the authentication procedure has been (successfully) finished if a dateOfCompletion is set.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->dateOfCompletion !== null;
    }

    /**
     * @param AuthenticationProcedure $other
     * @return bool
     */
    public function equals(AuthenticationProcedure $other): bool
    {
        $isSameServiceProvider = $this->serviceProvider->equals($other->serviceProvider);

        if ($this->identityProvider === null && $other->identityProvider === null) {
            $isSameIdentityProvider = true;
        } elseif ($this->identityProvider === null || $other->identityProvider === null) {
            $isSameIdentityProvider = false;
        } else {
            $isSameIdentityProvider = $this->identityProvider->equals($other->identityProvider);
        }

        $isSameDateOfCompletion = $this->dateOfCompletion === $other->dateOfCompletion;

        return $isSameServiceProvider && $isSameIdentityProvider && $isSameDateOfCompletion;
    }
}
