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

class EngineBlock_Attributes_ValidationResult
{
    /**
     * @var bool
     */
    private $success;

    /**
     * @var array
     */
    private $warnings;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var array
     */
    private $definitions;

    /**
     * @var array
     */
    private $errors;

    /**
     * @var array
     */
    private $validAttributes;

    public function __construct(
        array $attributes,
        array $definitions,
        $success,
        array $validAttributes,
        array $warnings,
        array $errors
    ) {
        $this->attributes = $attributes;
        $this->definitions = $definitions;
        $this->success = $success;
        $this->validAttributes = $validAttributes;
        $this->warnings = $warnings;
        $this->errors = $errors;
    }

    public function isValid($attributeName = null)
    {
        return empty($attributeName) ? $this->success: in_array($attributeName, $this->validAttributes);
    }

    public function hasWarnings($attributeName = null)
    {
        return empty($attributeName) ? empty($this->warnings) : empty($this->warnings[$attributeName]);
    }

    public function getWarnings($attributeName = null)
    {
        if (is_null($attributeName)) {
            return $this->warnings;
        }

        if (empty($this->warnings[$attributeName])) {
            return array();
        }

        return $this->warnings[$attributeName];
    }

    public function getWarningsForMissingAttributes()
    {
        $warnings = array();
        $attributesInWarning = array_keys($this->warnings);
        foreach ($attributesInWarning as $attributeInWarning) {
            if (!isset($this->attributes[$attributeInWarning])) {
                $warnings = array_merge($warnings, $this->warnings[$attributeInWarning]);
            }
        }
        return $warnings;
    }

    public function hasErrors($attributeName = null)
    {
        return empty($attributeName) ? !empty($this->errors) : !empty($this->errors[$attributeName]);
    }

    public function getErrors($attributeName = null)
    {
        if (is_null($attributeName)) {
            return $this->errors;
        }
        if (empty($this->errors[$attributeName])) {
            return array();
        }
        return $this->errors[$attributeName];
    }

    public function getErrorsForMissingAttributes()
    {
        $errors = array();
        $attributesInError = array_keys($this->errors);
        foreach ($attributesInError as $attributeInError) {
            // Skip aliases for missing attributes
            if (!empty($this->definitions[$attributeInError]['__original__'])) {
                continue;
            }

            if (!isset($this->attributes[$attributeInError])) {
                $errors = array_merge($errors, $this->errors[$attributeInError]);
            }
        }
        return $errors;
    }
}
