<?php

class EngineBlock_AttributeManipulator_File
{
    const FILE_NAME = 'manipulations.php';

    const ALLOWED_CHARACTERS_REGEX = '|[0-9a-zA-Z.-]|';

    protected $_fileLocation;

    public function manipulate(&$subjectId, array &$attributes, array &$response)
    {
        if (!$this->_setFileLocation()) {
            // If there is a problem with the file location, then we skip manipulation
            return;
        }
        $this->_doGeneralManipulation($subjectId, $attributes, $response);
        $this->_doSpSpecificManipulation($subjectId, $attributes, $response);
    }

    protected function _doGeneralManipulation(&$subjectId, &$attributes, &$response)
    {
        $file = $this->_fileLocation . DIRECTORY_SEPARATOR . self::FILE_NAME;
        if (!$this->_fileExists($file)) {
            return;
        }

        $this->_verifyPhpSyntax($file);

        $this->_include($file, $subjectId, $attributes, $response);
    }

    protected function _doSpSpecificManipulation(&$subjectId, &$attributes, &$response)
    {
        $spEntityId = $this->_getSpEntityIdFromResponse($response);
        $file = $this->_fileLocation .
                DIRECTORY_SEPARATOR .
                $this->_getDirectoryNameForEntityId($spEntityId) .
                DIRECTORY_SEPARATOR .
                self::FILE_NAME;
        
        if (!$this->_fileExists($file)) {
            return;
        }
        
        $this->_verifyPhpSyntax($file);

        $this->_include($file, $subjectId, $attributes, $response);
    }

    protected function _getSpEntityIdFromResponse($response)
    {
        return $response['__']['destinationid'];
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
    
    protected function _setFileLocation()
    {
        $location = $this->_getFileLocationFromConfiguration();
        if (substr($location, 0, 1) !== '/') {
            $realLocation = realpath(ENGINEBLOCK_FOLDER_ROOT . $location);
            if ($realLocation === FALSE) {
                ebLog()->warn("Location '$location' does not exist, relative from the EngineBlock root: " . ENGINEBLOCK_FOLDER_ROOT);
                return false;
            }
            $location = $realLocation;
        }
        $this->_fileLocation = $location;
        return $this;
    }

    protected function _getFileLocationFromConfiguration()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->attributeManipulator->file->location;
    }
}
