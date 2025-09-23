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
use OpenConext\EngineBlock\Authentication\Value\CollabPersonUuid;
use OpenConext\EngineBlockBundle\Authentication\Entity\SamlPersistentId;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\OpenConext\EngineBlockBundle\Authentication\Entity\SamlPersistentId>
 */
class SamlPersistentIdRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SamlPersistentId::class);
    }

    /**
     * @param CollabPersonUuid $uuid
     * @return SamlPersistentId[]
     */
    public function findByUuid(CollabPersonUuid $uuid)
    {
        return $this->findBy([
            'userUuid' => $uuid->getUuid(),
        ]);
    }

    /**
     * @param CollabPersonUuid $uuid
     */
    public function deleteByUuid(CollabPersonUuid $uuid)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->delete(SamlPersistentId::class, 'p')
            ->where('p.userUuid = :uuid')
            ->setParameter('uuid', $uuid->getUuid())
            ->getQuery()
            ->execute();
    }
}
