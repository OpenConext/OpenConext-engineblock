<?php

class EngineBlock_Corto_Filter_Command_NormalizeAttributes extends EngineBlock_Corto_Filter_Command_Abstract
    implements EngineBlock_Corto_Filter_Command_ResponseAttributesModificationInterface
{
    /**
     * {@inheritdoc}
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
