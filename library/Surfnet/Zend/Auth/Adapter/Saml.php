<?php
/**
 * SURFconext Manage
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
 * @category  SURFconext Manage
 * @package
 * @copyright Copyright © 2010-2011 SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

class Surfnet_Zend_Auth_Adapter_Saml implements Zend_Auth_Adapter_Interface
{
    /**
     * Performs an authentication attempt using SimpleSAMLphp
     *
     * @throws Zend_Auth_Adapter_Exception If authentication cannot be performed
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $authenticator = $this->_getAuthenticator();

        $authenticator->requireAuth();


        // If SimpleSAMLphp didn't stop it, then the user is logged in.

        return new Zend_Auth_Result(
            Zend_Auth_Result::SUCCESS,
            $authenticator->getAttributes(),
            array("Authentication Successful")
        );
    }

    public function getEntityId()
    {
        $authenticator = $this->_getAuthenticator();

        $authSource = $authenticator->getAuthSource();
        if (!$authSource instanceof sspmod_saml_Auth_Source_SP) {
            throw new Exception('Authenticator is not SAML?');
        }
        /** @var $authSource sspmod_saml_Auth_Source_SP */

        return $authSource->getEntityId();
    }

    protected function _getAuthenticator()
    {
        require_once(ENGINEBLOCK_FOLDER_VENDOR . 'simplesamlphp/simplesamlphp/lib/_autoload.php');
        return new SimpleSAML_Auth_Simple('default-sp');
    }
}