<?php

class EngineBlock_Corto_Filter_Command_NormalizeAttributes extends EngineBlock_Corto_Filter_Command_Abstract
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
        $normalizer = new EngineBlock_Attributes_Normalizer($this->_responseAttributes);
        $this->_responseAttributes = $normalizer->normalize();
    }
}