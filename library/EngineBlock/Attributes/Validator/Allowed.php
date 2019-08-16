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
