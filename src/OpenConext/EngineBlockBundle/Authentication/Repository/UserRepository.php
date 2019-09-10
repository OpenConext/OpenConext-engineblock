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

use Doctrine\ORM\EntityRepository;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlockBundle\Authentication\Entity\User;

/**
 *
 */
class UserRepository extends EntityRepository
{
    /**
     * @param User $user
     */
    public function save(User $user)
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @param CollabPersonId $collabPersonId
     * @return null|User
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByCollabPersonId(CollabPersonId $collabPersonId)
    {
        return $this
            ->createQueryBuilder('u')
            ->where('u.collabPersonId = :collabPersonId')
            ->setParameter('collabPersonId', $collabPersonId->getCollabPersonId())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param CollabPersonId $collabPersonId
     */
    public function deleteUserWithCollabPersonId(CollabPersonId $collabPersonId)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder
            ->delete($this->_entityName, 'u')
            ->where('u.collabPersonId = :collabPersonId')
            ->setParameter('collabPersonId', $collabPersonId->getCollabPersonId())
            ->getQuery()
            ->execute();

        $this->getEntityManager()->flush();
    }
}
