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

class EngineBlock_AttributesManipulator_FileMock extends EngineBlock_Attributes_Manipulator_File
{
    protected static $_mockFileLocation;

    public static function _getDirectoryNameForEntityId($entityId)
    {
        return parent::_getDirectoryNameForEntityId($entityId);
    }

    public static function setMockFileLocation($filePath)
    {
        self::$_mockFileLocation = $filePath;
    }

    public function _setRootLocation()
    {
        $this->_rootLocation = self::$_mockFileLocation;
        return $this;
    }

    /**
     * @return Zend_Config
     */
    protected function _getConfiguration()
    {
        return new Zend_Config(array('verify' => true));
    }
}
