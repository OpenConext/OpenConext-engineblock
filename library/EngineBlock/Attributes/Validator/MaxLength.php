<?php

class EngineBlock_Attributes_Validator_MaxLength extends EngineBlock_Attributes_Validator_Abstract
{
    const ERROR_ATTRIBUTE_VALIDATOR_MAXLENGTH = 'error_attribute_validator_maxlength';

    public function validate(array $attributes)
    {
        if (empty($attributes[$this->_attributeName])) {
            return true;
        }

        foreach ($attributes[$this->_attributeName] as $attributeValue) {
            if (strlen($attributeValue) > $this->_options) {
                $this->_messages[] = array(
                    self::ERROR_ATTRIBUTE_VALIDATOR_MAXLENGTH,
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
