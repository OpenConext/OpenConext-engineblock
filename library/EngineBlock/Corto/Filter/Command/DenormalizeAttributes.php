<?php

class EngineBlock_Corto_Filter_Command_DenormalizeAttributes extends EngineBlock_Corto_Filter_Command_Abstract
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
        if ($this->_serviceProvider->skipDenormalization) {
            return;
        }

        $normalizer = new EngineBlock_Attributes_Normalizer($this->_responseAttributes);
        $this->_responseAttributes = $normalizer->denormalize();
    }
}
