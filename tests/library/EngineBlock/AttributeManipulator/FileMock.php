<?php

class EngineBlock_AttributeManipulator_FileMock extends EngineBlock_AttributeManipulator_File
{
    protected static $_mockFileLocation;

    public static function _getDirectoryNameForEntityId($entityId)
    {
        return parent::_getDirectoryNameForEntityId($entityId);
    }

    public static function _getFileLocation()
    {
        return self::$_mockFileLocation;
    }

    public function _setFileLocation($filePath)
    {
        self::$_mockFileLocation = $filePath;
        return $this;
    }
}
