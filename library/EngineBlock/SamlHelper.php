<?php
/**
 * Temporary workaround
 *
 * @todo move methods message and entity objects *
 */
class EngineBlock_SamlHelper
{
    /**
     * @param array $sp
     * @param array $idp
     * @return bool
     */
    public static function doRemoteEntitiesRequireAdditionalLogging(array $sp, array $idp = null) {
        return (!empty($sp['AdditionalLogging']) || !empty($idp['AdditionalLogging']));
    }
}
