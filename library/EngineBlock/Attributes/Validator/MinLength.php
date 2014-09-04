<?php

class EngineBlock_Attributes_Validator_MinLength extends EngineBlock_Attributes_Validator_Abstract
{
    const ERROR_ATTRIBUTE_VALIDATOR_MINLENGTH = 'error_attribute_validator_minlength';

    public function validate(array $attributes)
    {
        if (empty($attributes[$this->_attributeName])) {
            return true;
        }

        foreach ($attributes[$this->_attributeName] as $attributeValue) {
            if (strlen($attributeValue) < $this->_options) {
                $this->_messages[] = array(
                    self::ERROR_ATTRIBUTE_VALIDATOR_MINLENGTH,
                    $this->_attributeName,
                    $this->_options,
                    $attributeValue
                );
                return false;
            }
        }
        return true;
    }
}
