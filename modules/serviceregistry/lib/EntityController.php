<?php

class sspmod_serviceregistry_EntityController extends sspmod_janus_EntityController
{
    public function getMetadataCaching()
    {
        $currentEntity = $this->getEntity();
        $st = $this->execute(
            'SELECT metadata_valid_until, metadata_cache_until
            FROM '. self::$prefix .'entity
            WHERE `eid` = ? AND `revisionid` = ?;',
            array($currentEntity->getEid(), $currentEntity->getRevisionid())
        );

        if ($st === false) {
            SimpleSAML_Logger::error(
                'JANUS:EntityController:_loadMetadata - Metadata could not load.'
            );
            return false;
        }
        $rs = $st->fetchAll(PDO::FETCH_ASSOC);
        return array(
            'validUntil' => strtotime($rs[0]['metadata_valid_until']),
            'cacheUntil' => strtotime($rs[0]['metadata_cache_until'])
        );
    }

    public function setMetadataCaching($validUntil, $cacheUntil)
    {
        $currentEntity = $this->getEntity();
        $query = 'UPDATE '. self::$prefix .'entity
            SET metadata_valid_until = ?, metadata_cache_until = ?
            WHERE `eid` = ? AND `revisionid` = ?;';
        $params = array(
            date('Y-m-d H:i:s', $validUntil),
            date('Y-m-d H:i:s', $cacheUntil),
            $currentEntity->getEid(),
            $currentEntity->getRevisionid()
        );
        return $this->execute($query, $params);

    }
}