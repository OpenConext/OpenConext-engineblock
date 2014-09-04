<?php

class EngineBlock_Attributes_Validator_Regex extends EngineBlock_Attributes_Validator_Abstract
{
    const ERROR_ATTRIBUTE_VALIDATOR_MAXLENGTH = 'error_attribute_validator_regex';

    public function validate(array $attributes)
    {
        if (empty($attributes[$this->_attributeName])) {
            return true;
        }

        foreach ($attributes[$this->_attributeName] as $attributeValue) {
            $matched = preg_match($this->_options, $attributeValue);
            if ($matched === false) {
                // @todo log
                $this->_messages[] = array(
                    self::ERROR_ATTRIBUTE_VALIDATOR_MAXLENGTH,
                    $this->_attributeName,
                    $this->_options,
                    $attributeValue
                );
                return false;
            }
            if ($matched === 0) {
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
