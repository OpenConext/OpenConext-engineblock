<?php

class EngineBlock_Corto_Filter_Command_AddOidAttributes extends EngineBlock_Corto_Filter_Command_Abstract
{
    /**
     * This command may modify the response attributes
     *
     * @return array
     */
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    public function execute()
    {
        $mapper = new EngineBlock_AttributeMapper_Urn2Oid();
        $oidResponseAttributes = $mapper->map($this->_responseAttributes);

        $this->_responseAttributes = array_merge($this->_responseAttributes, $oidResponseAttributes);
    }
}