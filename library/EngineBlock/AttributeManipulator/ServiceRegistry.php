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

class EngineBlock_AttributeManipulator_ServiceRegistry
{
    protected $_entityType;

    function __construct($entityType)
    {
        $this->_entityType = $entityType;
    }

    public function manipulate($entityId, &$subjectId, array &$attributes, array &$response)
    {
        $entity = $this->_getServiceRegistryAdapter()->getEntity($entityId);
        if (empty($entity['manipulation'])) {
            return false;
        }

        $this->_doManipulation($entity['manipulation'], $entityId, $subjectId, $attributes, $response);
        return true;
    }

    protected function _doManipulation($manipulationCode, &$entityId, &$subjectId, &$attributes, &$response)
    {
        eval($manipulationCode);
    }

    protected function _getServiceRegistryAdapter()
    {
        return new EngineBlock_Corto_ServiceRegistry_Adapter(
            new Janus_Client_CacheProxy()
        );
    }
}
