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

class EngineBlock_Provisioning
{
    /**
     * @var EngineBlock_UserDirectory
     */
    protected $_userDirectory = NULL;

    /**
     * @param  $saml2Attributes
     * @return string User Id of provisioned user.
     */
    public function provisionUser(array $saml2Attributes, array $spEntityMetadata, array $idpEntityMetadata)
    {
        $userId = $this->_getUserDirectory()->registerUser($saml2Attributes, $idpEntityMetadata);

        return $userId;
    }

    protected function _getUserDirectory()
    {
        if ($this->_userDirectory==NULL) {
            $ldapConfig = EngineBlock_ApplicationSingleton::getInstance()
                                                          ->getConfiguration()
                                                          ->ldap;
            $this->_userDirectory = new EngineBlock_UserDirectory($ldapConfig);
        }
        return $this->_userDirectory;
    }

    public function setUserDirectory($userDirectory)
    {
        $this->_userDirectory = $userDirectory;
    }
}
