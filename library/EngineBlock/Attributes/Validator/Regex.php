<?php

/**
 * Copyright 2014 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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
