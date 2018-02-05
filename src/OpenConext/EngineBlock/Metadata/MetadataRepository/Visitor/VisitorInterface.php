<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor;

use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;

/**
 * Interface VisitorInterface
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor
 */
interface VisitorInterface
{
    /**
     * @param IdentityProvider $identityProvider
     * @return IdentityProvider|null
     */
    public function visitIdentityProvider(IdentityProvider $identityProvider);

    /**
     * @param ServiceProvider $serviceProvider
     * @return ServiceProvider|null
     */
    public function visitServiceProvider(ServiceProvider $serviceProvider);

    /**
     * @param AbstractRole $role
     * @return AbstractRole|null
     */
    public function visitRole(AbstractRole $role);
}
