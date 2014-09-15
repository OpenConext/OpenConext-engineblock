<?php

class EngineBlock_Attributes_Validator_Max extends EngineBlock_Attributes_Validator_Abstract
{
    const ERROR_ATTRIBUTE_VALIDATOR_MAX = 'error_attribute_validator_max';

    public function validate(array $attributes)
    {
        if (empty($attributes[$this->_attributeName])) {
            return true;
        }

        $valueCount = count($attributes[$this->_attributeName]);
        if ($valueCount <= $this->_options) {
            return true;
        }

        $this->_messages[] = array(
            self::ERROR_ATTRIBUTE_VALIDATOR_MAX,
            $this->_attributeName,
            $this->_options,
            $valueCount
        );
        return false;
    }
}