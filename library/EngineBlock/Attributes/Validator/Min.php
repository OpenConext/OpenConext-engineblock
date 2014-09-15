<?php

class EngineBlock_Attributes_Validator_Min extends EngineBlock_Attributes_Validator_Abstract
{
    const ERROR_ATTRIBUTE_VALIDATOR_MIN = 'error_attribute_validator_min';

    public function validate(array $attributes)
    {
        if ((int)$this->_options <= 0) {
            return true;
        }

        if (empty($attributes[$this->_attributeName])) {
            $valueCount = 0;
        }
        else {
            $valueCount = count($attributes[$this->_attributeName]);
        }
        if ($valueCount >= $this->_options) {
            return true;
        }

        $this->_messages[] = array(
            self::ERROR_ATTRIBUTE_VALIDATOR_MIN,
            $this->_attributeAlias ? $this->_attributeAlias : $this->_attributeName,
            $this->_options,
            $valueCount
        );
        return false;
    }
}
