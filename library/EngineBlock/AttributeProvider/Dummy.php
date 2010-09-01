<?php
 
class EngineBlock_AttributeProvider_Dummy implements EngineBlock_AttributeProvider_Interface
{
    public function getIdentifier()
    {
        return 'dummy';
    }

    public function getAttributes($uid)
    {
        return array();
    }
}
