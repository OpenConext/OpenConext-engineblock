<?php
 
class EngineBlock_UserDirectoryMock extends EngineBlock_UserDirectory
{
    public function getCommonNameFromAttributes($attributes)
    {
        return $this->_getCommonNameFromAttributes($attributes);
    }
}
