<?php

namespace OpenConext\EngineBlockBundle\Authentication\Repository;

use Doctrine\ORM\EntityRepository;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonUuid;
use OpenConext\EngineBlockBundle\Authentication\Entity\SamlPersistentId;

class SamlPersistentIdRepository extends EntityRepository
{
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
