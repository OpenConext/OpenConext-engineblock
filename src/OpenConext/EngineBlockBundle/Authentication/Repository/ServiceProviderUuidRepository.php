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

namespace OpenConext\EngineBlockBundle\Authentication\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use OpenConext\EngineBlockBundle\Authentication\Entity\ServiceProviderUuid;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\OpenConext\EngineBlockBundle\Authentication\Entity\ServiceProviderUuid>
 */
class ServiceProviderUuidRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceProviderUuid::class);
    }

    public function findEntityIdByUuid(string $uuid): ?string
    {
        $entry = $this->findOneBy([
            'uuid' => $uuid,
        ]);

        if ($entry instanceof ServiceProviderUuid) {
            return $entry->serviceProviderEntityId;
        }

        return null;
    }

    /**
     * @param string $entityId
     * @return string|null  UUID string, or null if SP is not yet known
     */
    public function findUuidByEntityId(string $entityId): ?string
    {
        $entry = $this->findOneBy([
            'serviceProviderEntityId' => $entityId,
        ]);

        if ($entry instanceof ServiceProviderUuid) {
            return $entry->uuid;
        }

        return null;
    }
}
