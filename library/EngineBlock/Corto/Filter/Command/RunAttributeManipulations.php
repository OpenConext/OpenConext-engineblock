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

/**
 * Attribute / NameId / Response manipulation / mangling
 */
class EngineBlock_Corto_Filter_Command_RunAttributeManipulations extends EngineBlock_Corto_Filter_Command_Abstract
{
    const TYPE_SP  = 'sp';
    const TYPE_IDP = 'idp';

    private $_type;

    function __construct($type = '')
    {
        assert('in_array($type, array(self::TYPE_SP, self::TYPE_IDP, ""))');
        $this->_type = $type;
    }

    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * This command may modify the response attributes
     *
     * @return array
     */
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    public function execute()
    {
        $this->_response['__']['IntendedNameId'] = $this->_collabPersonId;

        $entityId = ($this->_type === self::TYPE_IDP) ?
            $this->_response['saml:Issuer']['__v'] :
            $this->_request['saml:Issuer']['__v'];

        // Try entity specific file based manipulation from Service Registry
        $manipulator = new EngineBlock_Attributes_Manipulator_ServiceRegistry($this->_type);
        $manipulated = $manipulator->manipulate(
            $entityId,
            $this->_response['__']['IntendedNameId'],
            $this->_responseAttributes,
            $this->_response,
            $this->_idpMetadata,
            $this->_spMetadata
        );
        return (bool)$manipulated;
    }
}
