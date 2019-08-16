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
