<?php

namespace OpenConext\EngineBlockBundle\Authentication\Repository;

use Doctrine\ORM\EntityRepository;
use OpenConext\EngineBlock\Assert\Assertion;
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
     * @param string $collabPersonId
     * @return null|User
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByCollabPersonId($collabPersonId)
    {
        Assertion::nonEmptyString($collabPersonId, 'collabPersonId');
        Assertion::length($collabPersonId, 64);

        return $this
            ->createQueryBuilder('u')
            ->where('u.collabPersonId = :collabPersonId')
            ->setParameter('collabPersonId', $collabPersonId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $collabPersonId
     */
    public function deleteUserWithCollabPersonId($collabPersonId)
    {
        Assertion::nonEmptyString($collabPersonId, 'collabPersonId');
        Assertion::length($collabPersonId, 64);

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
