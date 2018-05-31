<?php

namespace OpenConext\EngineBlockBundle\Authentication\Repository;

use Doctrine\ORM\EntityRepository;
use OpenConext\EngineBlockBundle\Authentication\Entity\ServiceProviderUuid;

class ServiceProviderUuidRepository extends EntityRepository
{
    /**
     * @param string $uuid
     * @return string|null
     */
    public function findEntityIdByUuid($uuid)
    {
        $entry = $this->findOneBy([
            'uuid' => $uuid,
        ]);

        if ($entry instanceof ServiceProviderUuid) {
            return $entry->serviceProviderEntityId;
        }
    }
}
