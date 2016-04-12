<?php

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
            ->setParameter('collabPersonId', $collabPersonId)
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
            ->setParameter('collabPersonId', $collabPersonId)
            ->getQuery()
            ->execute();

        $this->getEntityManager()->flush();
    }
}
