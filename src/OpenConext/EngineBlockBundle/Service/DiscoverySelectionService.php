<?php

/**
 * Copyright 2025 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Service;

use OpenConext\EngineBlock\Metadata\Discovery;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DiscoverySelectionService
{
    private const PREVIOUSLY_SELECTED_DISCOVERY_SESSION_NAME = 'discovery';
    public const USED_DISCOVERY_HASH_PARAM = 'discovery';

    public function discoveryMatchesHash(Discovery $discovery, string $hash): bool
    {
        return $this->hash($discovery) === $hash;
    }

    public function getDiscoveryFromRequest(SessionInterface $session, IdentityProvider $identityProvider): ?Discovery
    {
        $storedDiscoveryHash = $this->loadSelectedDiscoveryHashFromSession($session);
        foreach ($identityProvider->getDiscoveries() as $discovery) {
            if ($this->discoveryMatchesHash($discovery, $storedDiscoveryHash)) {
                return $discovery;
            }
        }
        return null;
    }

    public function hash(Discovery $discovery): string
    {
        $string = json_encode($discovery);
        return hash('sha256', $string);
    }

    public function registerDiscoveryHash(SessionInterface $session, string $hash): void
    {
        $session->set(self::PREVIOUSLY_SELECTED_DISCOVERY_SESSION_NAME, $hash);
    }

    public function clearDiscoveryHash(SessionInterface $session): void
    {
        $session->remove(self::PREVIOUSLY_SELECTED_DISCOVERY_SESSION_NAME);
    }

    private function loadSelectedDiscoveryHashFromSession(SessionInterface $session): string
    {
        $previousSelection = $session->get(self::PREVIOUSLY_SELECTED_DISCOVERY_SESSION_NAME, false);

        if ($previousSelection) {
            return $previousSelection;
        }

        return '';
    }
}
