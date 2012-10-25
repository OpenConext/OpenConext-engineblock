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

class EngineBlock_AttributeManipulator_File
{
    const FILE_NAME = 'manipulations.php';

    const ALLOWED_CHARACTERS_REGEX = '|[0-9a-zA-Z.-]|';

    protected $_rootLocation;
    protected $_directory;

    function __construct($directory = '')
    {
        $this->_directory = $directory;
    }

    /**
     * Manipulate for a given entity
     *
     * @param string $entityId
     * @param string $subjectId
     * @param array $attributes
     * @param array $response
     * @return bool
     */
    public function manipulate($entityId, &$subjectId, array &$attributes, array &$response)
    {
        if (!$this->_setRootLocation()) {
            // If there is a problem with the file location, then we skip manipulation
            return false;
        }

        $file = $this->_rootLocation .
                DIRECTORY_SEPARATOR .
                (!empty($entityId) ? $this->_getDirectoryNameForEntityId($entityId) . DIRECTORY_SEPARATOR : '') .
                self::FILE_NAME;
        if (!$this->_fileExists($file)) {
            return false;
        }

        if ($this->_getConfiguration()->lint) {
            $this->_verifyPhpSyntax($file);
        }

        $this->_include($file, $subjectId, $attributes, $response);
        return true;
    }

    protected function _fileExists($file)
    {
        return file_exists($file);
    }

    protected function _verifyPhpSyntax($file)
    {
        $lintCommand    = "php -l " . escapeshellarg($file);
        $lintOutput     = null;
        $lintExitStatus = null;

        exec($lintCommand, $lintOutput, $lintExitStatus);

        if ($lintExitStatus !== 0) {
            throw new EngineBlock_Exception("Lint error in '$file': " . implode(PHP_EOL, $lintOutput));
        }
    }

    protected function _include($filePath, &$subjectId, &$attributes, &$response)
    {
        EngineBlock_ApplicationSingleton::getLog()->info('Running Attribute Manipulation ' . $filePath);
        include $filePath;
    }

    protected static function _getDirectoryNameForEntityId($entityId)
    {
        $entityIdLength = strlen($entityId);
        $newEntityId = "";
        for ($i = 0; $i < $entityIdLength; $i++) {
            $character = substr($entityId, $i, 1);
            if (!preg_match(self::ALLOWED_CHARACTERS_REGEX, $character)) {
                $character = '_';
            }
            $newEntityId .= $character;
        }
        return $newEntityId;
    }
    
    protected function _setRootLocation()
    {
        $location = $this->_getConfiguration()->location;
        if (!empty($this->_directory)) {
            $location .= DIRECTORY_SEPARATOR . $this->_directory;
        }

        // Resolve path to files relative to EB root
        if (substr($location, 0, 1) !== '/') {
            $realLocation = realpath(ENGINEBLOCK_FOLDER_ROOT . $location);
            if ($realLocation === false) {
                EngineBlock_ApplicationSingleton::getLog()->warn(
                    "Location '$location' does not exist, ".
                    "relative from the EngineBlock root: " . realpath(ENGINEBLOCK_FOLDER_ROOT)
                );
                return false;
            }
            $location = $realLocation;
        }

        $this->_rootLocation = $location;
        return $this;
    }

    /**
     * @return Zend_Config
     */
    protected function _getConfiguration()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->attributeManipulator->file;
    }
}
