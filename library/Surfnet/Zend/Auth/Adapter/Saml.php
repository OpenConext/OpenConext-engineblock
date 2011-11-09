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
        require_once(LIBRARY_PATH . '/simplesamlphp/lib/_autoload.php');

        $as = new SimpleSAML_Auth_Simple('default-sp');
        $as->requireAuth();

        // If SimpleSAMLphp didn't stop it, then the user is logged in.

        return new Zend_Auth_Result(
            Zend_Auth_Result::SUCCESS,
            $as->getAttributes(),
            array("Authentication Successful")
        );
    }
}