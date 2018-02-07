<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor;

use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;

/**
 * Class DisableDisallowedEntitiesInWayfVisitor
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor
 */
class DisableDisallowedEntitiesInWayfVisitor implements VisitorInterface
{
    /**
     * @var array
     */
    private $allowedEntityIds;

    /**
     * @param $allowedEntityIds
     */
    public function __construct(array $allowedEntityIds)
    {
        $this->allowedEntityIds = $allowedEntityIds;
    }

    /**
     * {@inheritdoc}
     */
    public function visitIdentityProvider(IdentityProvider $identityProvider)
    {
        if (in_array($identityProvider->entityId, $this->allowedEntityIds)) {
            return;
        }

        $identityProvider->enabledInWayf = false;
    }

    /**
     * {@inheritdoc}
     */
    public function visitServiceProvider(ServiceProvider $serviceProvider)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function visitRole(AbstractRole $role)
    {
    }
}
