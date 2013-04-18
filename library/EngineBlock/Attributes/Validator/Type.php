<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
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
                $hostnameValidator = new Zend_Validate_Hostname();
                foreach ($attributeValues as $attributeValue) {
                    if (!$hostnameValidator->isValid($attributeValue)) {
                        $this->_messages[] = array(
                            self::ERROR_ATTRIBUTE_VALIDATOR_HOSTNAME,
                            $this->_attributeName,
                            $this->_options,
                            $attributeValue
                        );
                        return false;
                    }
                }
                break;            case 'URL':
                foreach ($attributeValues as $attributeValue) {
                    if (!Zend_Uri::check($attributeValue)) {
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
                $emailValidator = new Zend_Validate_EmailAddress();
                foreach ($attributeValues as $attributeValue) {
                    if (!$emailValidator->isValid($attributeValue)) {
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
                throw new EngineBlock_Exception("Unknown validate option '{$this->_options}' for attribute validation");
        }
        return true;
    }
}