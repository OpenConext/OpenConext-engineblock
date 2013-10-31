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

class EngineBlock_VirtualOrganization_Validator
{

    public function isMember($voId, $subjectId, $idp)
    {
        $virtualOrganization = new EngineBlock_VirtualOrganization($voId);
        $voType = $virtualOrganization->getType();

        switch ($voType) {
            case 'MIXED':
                if ($this->_isMemberOfIdps($virtualOrganization, $idp)) {
                    return true;
                }
                else if ($this->_isMemberOfGroups($virtualOrganization, $subjectId)) {
                    return true;
                }
                else {
                    return false;
                }

            case 'GROUP':
                if ($this->_isMemberOfGroups($virtualOrganization, $subjectId)) {
                    return true;
                }
                else {
                    return false;
                }

            case 'IDP':
                if ($this->_isMemberOfIdps($virtualOrganization, $idp)) {
                    return true;
                }
                else {
                    return false;
                }

            default:
                throw new EngineBlock_Exception("Unknown Virtual Organization type '$voType'");
        }
    }

    protected function _isMemberOfGroups(EngineBlock_VirtualOrganization $virtualOrganization, $subjectId)
    {
        $groups = $virtualOrganization->getGroupsIdentifiers();
        $groupValidator = new EngineBlock_VirtualOrganization_GroupValidator();
        return $groupValidator->isMember($subjectId, $groups);
    }

    protected function _isMemberOfIdps(EngineBlock_VirtualOrganization $virtualOrganization, $idp)
    {
        $idpIdentifiers = $virtualOrganization->getIdpIdentifiers();
        foreach ($idpIdentifiers as $idpId) {
            if ($idpId === $idp) {
                return true;
            }
        }
        return false;
    }

}