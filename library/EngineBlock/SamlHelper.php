<?php
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProviderEntity;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProviderEntity;

/**
 * Temporary workaround
 *
 * @todo move methods message and entity objects *
 */
class EngineBlock_SamlHelper
{
    /**
     * @param ServiceProviderEntity $sp
     * @param IdentityProviderEntity $idp
     * @return bool
     */
    public static function doRemoteEntitiesRequireAdditionalLogging(ServiceProviderEntity $sp, IdentityProviderEntity $idp = null) {
        return $sp->additionalLogging || $idp->additionalLogging;
    }
}
