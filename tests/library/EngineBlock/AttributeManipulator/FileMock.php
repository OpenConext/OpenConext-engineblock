<?php

class EngineBlock_AttributeManipulator_FileMock extends EngineBlock_AttributeManipulator_File
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

    public function _setFileLocation()
    {
        $this->_fileLocation = self::$_mockFileLocation;
        return $this;
    }
}
