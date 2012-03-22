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
 * Adds group the current user is a member of
 */
class EngineBlock_Corto_Filter_Command_AddVoMemberships extends EngineBlock_Corto_Filter_Command_Abstract
{
    const URN_IS_MEMBER_OF          = 'urn:mace:dir:attribute-def:isMemberOf';
    const URN_VO_PREFIX             = 'urn:collab:org:';

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

        if (!$this->_spMetadata['ProvideIsMemberOf']) {
            return;
        }

        if (!isset($this->_responseAttributes[self::URN_IS_MEMBER_OF])) {
            $this->_responseAttributes[self::URN_IS_MEMBER_OF] = array();
        }
        $groups = &$this->_responseAttributes[self::URN_IS_MEMBER_OF];
        $voValidator  = new EngineBlock_VirtualOrganization_Validator();
        $voCollection = new EngineBlock_VirtualOrganization_Collection();
        foreach ($voCollection->load() as $vo) {
            $isMember = $voValidator->isMember($vo->getId(), $this->_collabPersonId, $this->_idpMetadata["EntityId"]);
            if ($isMember) {
                $groups[] = self::URN_VO_PREFIX . $vo->getId();
            }
        }
    }
}