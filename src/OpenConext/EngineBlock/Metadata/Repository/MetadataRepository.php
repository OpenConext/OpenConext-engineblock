<?php

namespace OpenConext\EngineBlock\Metadata\Repository;

use OpenConext\Value\Saml\EntityId;

interface MetadataRepository
{
    public function getSamlEntityBy(EntityId $entityId);
    public function getServiceProviderBy(EntityId $entityId);
    public function getIdentityProviderBy(EntityId $entityId);
}
