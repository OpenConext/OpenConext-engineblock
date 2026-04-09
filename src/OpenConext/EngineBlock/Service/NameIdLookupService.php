<?php

/**
 * Copyright 2026 SURFnet B.V.
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

namespace OpenConext\EngineBlock\Service;

use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonUuid;
use OpenConext\EngineBlock\Authentication\Value\SchacHomeOrganization;
use OpenConext\EngineBlock\Authentication\Value\Uid;
use OpenConext\EngineBlockBundle\Authentication\Entity\SamlPersistentId;
use OpenConext\EngineBlockBundle\Authentication\Repository\SamlPersistentIdRepository;
use OpenConext\EngineBlockBundle\Authentication\Repository\ServiceProviderUuidRepository;
use OpenConext\EngineBlockBundle\Authentication\Repository\UserRepository;
use Psr\Log\LoggerInterface;

final class NameIdLookupService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ServiceProviderUuidRepository $spUuidRepository,
        private readonly SamlPersistentIdRepository $persistentIdRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function resolveNameId(string $schacHomeOrganization, string $uid, string $spEntityId): ?NameIdResult
    {
        $collabPersonId = CollabPersonId::generateWithReplacedAtSignFrom(
            new Uid($uid),
            new SchacHomeOrganization($schacHomeOrganization)
        );

        $user = $this->userRepository->findByCollabPersonId($collabPersonId);
        if ($user === null) {
            $this->logger->debug('NameIdLookupService: user not found', [
                'collabPersonId' => $collabPersonId->getCollabPersonId(),
            ]);
            return null;
        }

        $spUuid = $this->spUuidRepository->findUuidByEntityId($spEntityId);
        if ($spUuid === null) {
            $this->logger->debug('NameIdLookupService: SP not found', ['spEntityId' => $spEntityId]);
            return null;
        }

        $userUuid = $user->collabPersonUuid->getUuid();
        $stored = $this->persistentIdRepository->findByUserAndSpUuid($userUuid, $spUuid);

        if ($stored !== null) {
            return new NameIdResult($stored->persistentId, true);
        }

        return new NameIdResult(SamlPersistentId::generate($userUuid, $spUuid)->persistentId, false);
    }

    public function resolveUserIdentity(string $persistentId): ?UserIdentityResult
    {
        $entry = $this->persistentIdRepository->find($persistentId);
        if ($entry === null) {
            return null;
        }

        $user = $this->userRepository->findByCollabPersonUuid(new CollabPersonUuid($entry->userUuid));
        if ($user === null) {
            $this->logger->warning(
                'NameIdLookupService: saml_persistent_id entry exists but user record is missing',
                ['userUuid' => $entry->userUuid]
            );
            return null;
        }

        $spEntityId = $this->spUuidRepository->findEntityIdByUuid($entry->serviceProviderUuid);
        if ($spEntityId === null) {
            $this->logger->warning(
                'NameIdLookupService: saml_persistent_id entry exists but SP UUID record is missing',
                ['serviceProviderUuid' => $entry->serviceProviderUuid]
            );
            return null;
        }

        return new UserIdentityResult(
            $user->collabPersonId->getSchacHomeOrganization(),
            $user->collabPersonId->getStoredUid(),
            $spEntityId,
        );
    }
}
