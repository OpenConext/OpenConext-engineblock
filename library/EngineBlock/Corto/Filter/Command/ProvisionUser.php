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

class EngineBlock_Corto_Filter_Command_ProvisionUser extends EngineBlock_Corto_Filter_Command_Abstract
{
    /**
     * This command modifies the response
     *
     * @return array
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * This command modifies the collabPersonId
     *
     * @return string
     */
    public function getCollabPersonId()
    {
        return $this->_collabPersonId;
    }

    public function execute()
    {
        // Provisioning of the user account
        $subjectId = $this->_getProvisioning()->provisionUser(
            $this->_responseAttributes,
            $this->_spMetadata,
            $this->_idpMetadata
        );

        $this->setCollabPersonId($subjectId);

        $this->_response['__']['collabPersonId'] = $subjectId;
        $this->_response['__']['OriginalNameId'] = $this->_response['saml:Assertion']['saml:Subject']['saml:NameID'];
        // Adjust the NameID in the OLD response (for consent), set the collab:person uid
        $this->_response['saml:Assertion']['saml:Subject']['saml:NameID'] = array(
            '_Format' => EngineBlock_Urn::SAML2_0_NAMEID_FORMAT_PERSISTENT,
            '__v'     => $subjectId
        );
    }

    protected function _getProvisioning()
    {
        return new EngineBlock_Provisioning();
    }
}