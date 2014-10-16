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
    public static function doRemoteEntitiesRequireAdditionalLogging(array $entities) {
        foreach ($entities as $entity) {
            if (!empty($entity['AdditionalLogging'])) {
                return true;
            }
        }
        return false;
    }
}
