<?php
/**
 * SURFconext EngineBlock
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
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

class EngineBlock_VirtualOrganization
{
    protected $_id;
    protected $_data;
    protected $_db;

    public function __construct($id)
    {
        $this->_id = $id;
    }

    public function setId($id)
    {
        $this->_id  = $id;
        return $id;
    }

    public function getGroups()
    {
        $this->_load();

        // Get the records
        $db = $this->_getDbConnection();
        $stmt = $db->prepare(
            "SELECT vog.group_id, vog.group_stem
             FROM `virtual_organisation_group` vog
             WHERE vog.vo_id = ?"
        );
        $stmt->execute(array($this->_id));
        $groupRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Map them to value objects
        $groups = array();
        foreach ($groupRecords as $groupRecord) {
            $group = new EngineBlock_VirtualOrganization_Group();
            $group->id = $groupRecord['group_id'];
            $group->stem = $groupRecord['group_stem'];
            $groups[] = $group;
        }
        return $groups;
    }

    public function getIdps()
    {
        $this->_load();

        $db = $this->_getDbConnection();
        $stmt = $db->prepare(
            "SELECT voi.idp_id
             FROM `virtual_organisation_idp` voi
             WHERE voi.vo_id = ?"
        );
        $stmt->execute(array($this->_id));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function _load()
    {
        $db = $this->_getDbConnection();
        $stmt = $db->prepare(
            "SELECT vo.vo_id
             FROM `virtual_organisation` vo
             WHERE vo.vo_id = ?
             LIMIT 0,1"
        );
        $stmt->execute(array($this->_id));
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (empty($data)) {
            throw new EngineBlock_VirtualOrganization_VoIdentifierNotFoundException(
                "No data found for Virtual Organization '{$this->_id}'"
            );
        }

        $this->_data = $data;
    }

    protected function _getDbConnection()
    {
        if (isset($this->_db)) {
            return $this->_db;
        }

        $factory = new EngineBlock_Database_ConnectionFactory();
        $this->_db = $factory->create(EngineBlock_Database_ConnectionFactory::MODE_READ);

        return $this->_db;
    }
}