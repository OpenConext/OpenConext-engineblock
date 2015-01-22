<?php

class EngineBlock_Attributes_Validator_Allowed extends EngineBlock_Attributes_Validator_Abstract
{
    const ERROR_ATTRIBUTE_VALIDATOR_ALLOWED = 'error_attribute_validator_allowed';

    public function validate(array $attributes)
    {
        if (!is_array($this->_options)) {
            return null;
        }

        if (empty($attributes[$this->_attributeName])) {
            return null;
        }

        $attributeValid = true;
        foreach ($attributes[$this->_attributeName] as $attributeValue) {
            if (in_array($attributeValue, $this->_options)) {
                continue;
            }

            $attributeValid = false;
            $this->_messages[] = array(
                static::ERROR_ATTRIBUTE_VALIDATOR_ALLOWED,
                $this->_attributeAlias ? $this->_attributeAlias : $this->_attributeName,
                $this->_options,
                $attributeValue,
            );
        }
        return $attributeValid;
    }
}
