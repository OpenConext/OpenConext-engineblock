<?php
/**
 * SURFconext Service Registry
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext Service Registry
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

class sspmod_serviceregistry_EntityController extends sspmod_janus_EntityController
{
    /**
     * @throws Exception
     * @return OpenSsl_Certificate
     */
    public function getCertificate()
    {
        $metadata = $this->getMetaArray();
        if (!isset($metadata['certData']) || trim($metadata['certData'])==="") {
            throw new Janus_Exception_NoCertData("Unable to create certificate object, certData metadata missing!");
        }
        return Janus_CertificateFactory::create($metadata['certData']);
    }

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