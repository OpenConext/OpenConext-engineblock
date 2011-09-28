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

class EngineBlock_AttributeMetadata
{
    protected $_attributeMetadata;

    public function load()
    {
        $this->_attributeMetadata = $this->_loadFromFile();
    }

    protected function _loadFromFile()
    {
        // Initialize the attributes
        $attributes = array();
        require ENGINEBLOCK_FOLDER_APPLICATION . 'configs/attributes.inc.php';
        return $attributes;
    }

    public function getName($attributeId, $ietfLanguageTag = 'en_US')
    {
        if (!isset($this->_attributeMetadata)) {
            $this->load();
        }

        $name = $this->_getDataType('Name', $attributeId, $ietfLanguageTag);
        if (!$name) {
            $name = $attributeId;
        }
        return $name;
    }

    public function getDescription($attributeId, $ietfLanguageTag = 'en_US')
    {
        if (!isset($this->_attributeMetadata)) {
            $this->load();
        }

        $description = $this->_getDataType('Description', $attributeId, $ietfLanguageTag);
        if (!$description) {
            $description = '';
        }
        return $description;
    }

    protected function _getDataType($type, $id, $ietfLanguageTag = 'en_US')
    {
        if (isset($this->_attributeMetadata[$id][$type][$ietfLanguageTag])) {
            return $this->_attributeMetadata[$id][$type][$ietfLanguageTag];
        }
        // @todo warn the system! requested an unknown UID or language...
        return $id;
    }
}