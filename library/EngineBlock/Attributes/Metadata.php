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

class EngineBlock_Attributes_Metadata
{
    protected $_definitions;
    protected $_logger;

    public function getName($attributeId, $ietfLanguageTag = 'en', $fallbackToId = true)
    {
        $this->_loadAttributeDefinitions();

        $name = $this->_getDataType($attributeId, 'Name', $ietfLanguageTag);
        if (!$name && $fallbackToId) {
            $name = $attributeId;
        }
        return $name;
    }

    public function getDescription($attributeId, $ietfLanguageTag = 'en')
    {
        $this->_loadAttributeDefinitions();

        $description = $this->_getDataType($attributeId, 'Description', $ietfLanguageTag, $fallbackToId = true);
        if (!$description && $fallbackToId) {
            $description = '';
        }
        return $description;
    }

    protected function _getDataType($id, $type, $ietfLanguageTag = 'en')
    {
        $this->_loadLogger();

        if (isset($this->_definitions[$id][$type][$ietfLanguageTag])) {
            return $this->_definitions[$id][$type][$ietfLanguageTag];
        }
        $this->_logger->log(
            "Attribute lookup failure '$id' has no '$type' for language '$ietfLanguageTag'", EngineBlock_Log::NOTICE
        );
        return $id;
    }

    protected function _loadAttributeDefinitions()
    {
        static $s_defaultDefinitions;

        // Definitions loading default
        if (isset($this->_definitions)) {
            return $this->_definitions;
        }

        if (isset($s_defaultDefinitions)) {
            $this->_definitions = $s_defaultDefinitions;
            return $this->_definitions;
        }

        $s_defaultDefinitions = json_decode(
            file_get_contents(
                EngineBlock_ApplicationSingleton::getInstance()->getConfigurationValue(
                    'attributeDefinitionFile',
                    ENGINEBLOCK_FOLDER_APPLICATION . 'configs/attributes.json'
                )
            ),
            true
        );
        $this->_definitions = $s_defaultDefinitions;
        return $this->_definitions;
    }

    public function setDefinition($definitions)
    {
        $this->_definitions = $definitions;
        return $this;
    }

    public function setLogger(Zend_Log $logger)
    {
        $this->_logger = $logger;
        return $this;
    }

    protected function _loadLogger()
    {
        if (!isset($this->_logger)) {
            $this->setLogger(EngineBlock_ApplicationSingleton::getLog());
        }
        return $this->_logger;
    }
}