<?php

class EngineBlock_AttributeManipulator_File
{
    const FILE_NAME = 'manipulations.php';

    const ALLOWED_CHARACTERS_REGEX = '|[0-9a-zA-Z.-]|';

    protected $_fileLocation;

    public function manipulate($subjectId, array $attributes, array $response)
    {
        if (floatval(phpversion()) >= 5.3) {
            // PHP 5.2 lexer doesn't enjoy the PHP 5.3 Late Static Binding 'static' keyword
            // for now we want compatibility, but we also want to make this testable,
            // so to compromise we use eval... for now...
            $this->_fileLocation = eval("static::_getFileLocation()");
        }
        else {
            $this->_fileLocation = self::_getFileLocation();            
        }
        $attributes = $this->_doGeneralManipulation($subjectId, $attributes, $response);
        $attributes = $this->_doSpSpecificManipulation($subjectId, $attributes, $response);
        return $attributes;
    }

    protected function _doGeneralManipulation($subjectId, $attributes, $response)
    {
        $file = $this->_fileLocation . DIRECTORY_SEPARATOR . self::FILE_NAME;

        if (!$this->_fileExists($file)) {
            return $attributes;
        }

        $this->_verifyPhpSyntax($file);

        return $this->_include($file, $subjectId, $attributes, $response);
    }

    protected function _doSpSpecificManipulation($subjectId, $attributes, $response)
    {
        $spEntityId = $response['_Destination'];
        $file = $this->_fileLocation .
                DIRECTORY_SEPARATOR .
                $this->_getDirectoryNameForEntityId($spEntityId) .
                DIRECTORY_SEPARATOR .
                self::FILE_NAME;

        if (!$this->_fileExists($file)) {
            return $attributes;
        }
        
        $this->_verifyPhpSyntax($file);

        return $this->_include($file, $subjectId, $attributes, $response);
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

    protected function _include($filePath, $subjectId, $attributes, $response)
    {
        include $filePath;
        return $attributes;
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

    protected static function _getFileLocation()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->attributeManipulator->file->location;
    }
}
