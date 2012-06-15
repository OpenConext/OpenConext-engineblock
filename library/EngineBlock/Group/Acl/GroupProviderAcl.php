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


class EngineBlock_Group_Acl_GroupProviderAcl
{
    /**
     * @var EngineBlock_Database_ConnectionFactory
     */
    protected $_factory = NULL;

    /**
     * Get all ServiceProviderGroupAcls (array where the key is the identifier
     * with as value an array of permissions
     *
     * @param $spEntityId the identifier of the Service Provider
     * @return array with the identifier being the group provider
     */
    public function getSpGroupAcls($spEntityId) {
        $db = $this->_getReadDatabase();
        $statement = $db->prepare('SELECT gp.identifier, spga.allow_groups, spga.allow_members FROM service_provider_group_acl spga, group_provider gp WHERE spga.group_provider_id = gp.id and spga.spentityid = ?');
        $statement->execute(array($spEntityId));
        $rows = $statement->fetchAll();
        $spGroupAcls = array();
        foreach ($rows as $row) {
            $spGroupAcls[$row['identifier']] = array(
                        'allow_groups'        => $row['allow_groups'],
                        'allow_members'       => $row['allow_members'],
            );
        }
        return $spGroupAcls;
    }

    protected function _getReadDatabase()
    {
        if ($this->_factory == NULL) {
            $this->_factory = new EngineBlock_Database_ConnectionFactory();
        }
        return $this->_factory->create(EngineBlock_Database_ConnectionFactory::MODE_READ);
    }
}
