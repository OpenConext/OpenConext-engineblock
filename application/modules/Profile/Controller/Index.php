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

class Profile_Controller_Index extends EngineBlock_Controller_Abstract
{
    protected $_identity;

    protected $_attributes;

    public function indexAction()
    {
        // Require authentication
        $this->_initAuthentication();

        // Initialize the attributes
        $this->setAttributes();

        $this->__set('attributes', $this->_identity);
    }

    public function setAttributes()
    {
        $attributes = array();
        require ENGINEBLOCK_FOLDER_APPLICATION . 'configs/attributes.inc.php';

        $this->_attributes = $attributes;
    }

    public function getAttributeName($uid, $ietfLanguageTag = 'en_US')
    {
        $name = $this->_getAttributeDataType('Name', $uid, $ietfLanguageTag);
        if (!$name) {
            $name = $uid;
        }
        return $name;
    }

    public function getAttributeDescription($uid, $ietfLanguageTag = 'en_US')
    {
        $description = $this->_getAttributeDataType('Description', $uid, $ietfLanguageTag);
        if (!$description) {
            $description = '';
        }
        return $description;
    }

    protected function _getAttributeDataType($type, $name, $ietfLanguageTag = 'en_US')
    {
        if (isset($this->_attributes[$name][$type][$ietfLanguageTag])) {
            return $this->_attributes[$name][$type][$ietfLanguageTag];
        }
        // @todo warn the system! requested a unkown UID or langauge...
        return $name;
    }

    protected function _initAuthentication()
    {
        $this->_identity = EngineBlock_Authenticator::authenticate();
    }
}