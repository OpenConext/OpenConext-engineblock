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

class EngineBlock_Corto_Filter_Command_ValidateVoMembership extends EngineBlock_Corto_Filter_Command_Abstract
{
    const VO_NAME_ATTRIBUTE         = 'urn:oid:1.3.6.1.4.1.1076.20.100.10.10.2';

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
        if (!$this->_collabPersonId) {
            throw new EngineBlock_Corto_Filter_Command_Exception_PreconditionFailed(
                'Missing collabPersonId'
            );
        }

        // In filter stage we need to take a look at the VO context
        $vo = false;
        if (isset($this->_request['__']['VoContextImplicit'])) {
            $vo = $this->_request['__']['VoContextImplicit'];
        }
        else if(isset($this->_request['__'][EngineBlock_Corto_ProxyServer::VO_CONTEXT_PFX])) {
            $vo = $this->_request['__'][EngineBlock_Corto_ProxyServer::VO_CONTEXT_PFX];
        }

        if (!$vo) {
            return;
        }

        $this->_adapter->setVirtualOrganisationContext($vo);

        // If in VO context, validate the user's membership

        EngineBlock_ApplicationSingleton::getLog()->debug("VO $vo membership required");

        $validator = $this->_getValidator();
        $isMember = $validator->isMember(
            $vo,
            $this->_collabPersonId,
            $this->_idpMetadata['EntityId']
        );
        if (!$isMember) {
            throw new EngineBlock_Corto_Exception_UserNotMember("User not a member of VO $vo");
        }

        $this->_responseAttributes[self::VO_NAME_ATTRIBUTE] = $vo;

    }

    /**
     * @return EngineBlock_VirtualOrganization_Validator
     */
    protected function _getValidator()
    {
        return new EngineBlock_VirtualOrganization_Validator();
    }

}