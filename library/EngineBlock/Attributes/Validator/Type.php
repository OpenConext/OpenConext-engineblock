<?php

/**
 * Copyright 2010 SURFnet B.V.
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

class EngineBlock_Attributes_Validator_Type extends EngineBlock_Attributes_Validator_Abstract
{
    const ERROR_ATTRIBUTE_VALIDATOR_URI      = 'error_attribute_validator_type_uri';
    const ERROR_ATTRIBUTE_VALIDATOR_URN      = 'error_attribute_validator_type_urn';
    const ERROR_ATTRIBUTE_VALIDATOR_URL      = 'error_attribute_validator_type_url';
    const ERROR_ATTRIBUTE_VALIDATOR_HOSTNAME = 'error_attribute_validator_type_hostname';
    const ERROR_ATTRIBUTE_VALIDATOR_EMAIL    = 'error_attribute_validator_type_emailaddress';

    public function validate(array $attributes)
    {
        if (empty($attributes[$this->_attributeName])) {
            return true;
        }

        $attributeValues = $attributes[$this->_attributeName];

        switch($this->_options) {
            case 'URN':
                $urnValidator = new EngineBlock_Validator_Urn();
                foreach ($attributeValues as $attributeValue) {
                    if (!$urnValidator->validate($attributeValue)) {
                        $this->_messages[] = array(
                            self::ERROR_ATTRIBUTE_VALIDATOR_URN,
                            $this->_attributeName,
                            $this->_options,
                            $attributeValue
                        );
                        return false;
                    }
                }
                break;

            case 'HostName':
                foreach ($attributeValues as $attributeValue) {
                    // The goal of this validation is only to see if the value
                    // looks like a hostname, to make sure it is not a
                    // completely different value like a phone number, or a
                    // text containing newlines or null bytes. Checking if it
                    // is truely a valid hostname is not EngineBlocks
                    // responsibility.
                    //
                    // Below regex checks if the value is alfanumeric (or
                    // contains hyphens, which are also allowed in hostnames)
                    // and if the value does not contains only dots, or
                    // multiple subsequent dots.
                    if (!preg_match('/^([[:alnum:]\-]+\.)*[[:alnum:]0-9\-]+$/', $attributeValue)) {
                        $this->_messages[] = array(
                            self::ERROR_ATTRIBUTE_VALIDATOR_HOSTNAME,
                            $this->_attributeName,
                            $this->_options,
                            $attributeValue
                        );
                        return false;
                    }
                }
                break;

            case 'URL':
                foreach ($attributeValues as $attributeValue) {
                    if (filter_var($attributeValue, FILTER_VALIDATE_URL) === false) {
                        $this->_messages[] = array(
                            self::ERROR_ATTRIBUTE_VALIDATOR_URL,
                            $this->_attributeName,
                            $this->_options,
                            $attributeValue
                        );
                        return false;
                    }
                }
                break;

            case 'URI':
                $uriValidator = new EngineBlock_Validator_Uri();
                foreach ($attributeValues as $attributeValue) {
                    if (!$uriValidator->validate($attributeValue)) {
                        $this->_messages[] = array(
                            self::ERROR_ATTRIBUTE_VALIDATOR_URI,
                            $this->_attributeName,
                            $this->_options,
                            $attributeValue
                        );
                        return false;
                    }
                }
                break;

            case 'EmailAddress':
                foreach ($attributeValues as $attributeValue) {
                    if (filter_var($attributeValue, FILTER_VALIDATE_EMAIL) === false) {
                        $this->_messages[] = array(
                            self::ERROR_ATTRIBUTE_VALIDATOR_EMAIL,
                            $this->_attributeName,
                            $this->_options,
                            $attributeValue
                        );
                        return false;
                    }
                }
                break;

            default:
                throw new EngineBlock_Exception(
                    sprintf('Unknown validate option "%s" for attribute validation', $this->_options)
                );
        }
        return true;
    }
}
