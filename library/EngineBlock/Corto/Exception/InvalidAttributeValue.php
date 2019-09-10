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

/**
 * Exception thrown on attribute violation
 * This is thrown when validating the the assertion attributes on input filtering.
 */
class EngineBlock_Corto_Exception_InvalidAttributeValue extends EngineBlock_Exception implements EngineBlock_Corto_Exception_HasFeedbackInfoInterface
{
    private $attributeName;
    private $attributeValue;

    public function __construct($message, $attributeName, $attributeValue, $severity = self::CODE_NOTICE, Exception $previous = null)
    {
        parent::__construct($message, $severity, $previous);
        $this->attributeName = $attributeName;
        $this->attributeValue = $attributeValue;
    }

    public function getAttributeName()
    {
        return $this->attributeName;
    }

    public function getAttributeValue()
    {
        return $this->attributeValue;
    }

    /**
     * @return array
     */
    public function getFeedbackInfo()
    {
        return [
            'attributeName' => $this->attributeName,
            'attributeValue' => $this->attributeValue,
        ];
    }
}
