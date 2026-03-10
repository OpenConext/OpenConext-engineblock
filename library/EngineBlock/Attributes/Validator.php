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

class EngineBlock_Attributes_Validator
{
    /**
     * @var array
     */
    private $definitions;

    /**
     * @var EngineBlock_Attributes_Validator_Factory
     */
    private $validatorFactory;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var array
     */
    private $validAttributes;

    /**
     * @var array
     */
    private $warnings;

    /**
     * @var array
     */
    private $errors;

    private $identityProvider;

    public function __construct(array $definitions, EngineBlock_Attributes_Validator_Factory $validatorFactory)
    {
        $this->definitions = $definitions;
        $this->validatorFactory = $validatorFactory;
    }

    public function validate(array $attributes, $excluded = array(), $identityProvider = null)
    {
        $this->attributes = $attributes;
        $this->identityProvider = $identityProvider;
        $this->validAttributes = array();
        $this->warnings = array();
        $this->errors = array();

        $validAttributeSet = true;
        foreach ($this->definitions as $attributeName => $definition) {
            if (in_array($attributeName, $excluded)) {
                $this->validAttributes[] = $attributeName;
                continue;
            }

            $validAttribute = $this->validateAttribute($attributeName, $definition);

            if ($validAttribute) {
                $this->validAttributes[] = $attributeName;
                continue;
            }

            $validAttributeSet = false;
        }

        $attributesNotInDefinitions = array_diff(array_keys($attributes), array_keys($this->definitions));
        if (!empty($attributesNotInDefinitions)) {
            foreach ($attributesNotInDefinitions as $attributeName) {
                $this->warnings[$attributeName] = array(array(
                    'error_attribute_validator_not_in_definitions',
                    $attributeName,
                    null,
                    null
                ));
            }
            $validAttributeSet = false;
        }

        return new EngineBlock_Attributes_ValidationResult(
            $attributes,
            $this->definitions,
            $validAttributeSet,
            $this->validAttributes,
            $this->warnings,
            $this->errors
        );
    }

    /**
     *
     * Example: we know of an attribute 'dog' with an alias 'canine'.
     * Situation 1: Neither are in the set, so we only validate 'dog'.
     * Situation 2: 'dog' is in the set, so we validate that.
     * Situation 3: 'canine' is in the set, so we validate that.
     * Situation 4: Both are in the set, we validate both.
     *
     * @param $attributeName
     * @param $definition
     * @return bool
     */
    private function validateAttribute($attributeName, $definition)
    {
        if (empty($definition['Conditions'])) {
            return true;
        }

        // Excludes for Situation 1, 2 and 3 (see example in docBlock)
        $isInAttributeSet = !empty($this->attributes[$attributeName]);
        $isAnAlias = !empty($definition['__original__']);
        if (!$isInAttributeSet && $isAnAlias) {
            return true;
        }

        $validAttribute = true;
        $validAttribute = $validAttribute && $this->validateAttributeWarnings($attributeName, $definition);
        $validAttribute = $validAttribute && $this->validateAttributeErrors($attributeName, $definition);
        return $validAttribute;
    }

    /**
     * @param $attributeName
     * @param $definition
     * @return array
     * @throws EngineBlock_Exception
     */
    private function validateAttributeWarnings($attributeName, $definition)
    {
        if (empty($definition['Conditions']['warning'])) {
            return true;
        }

        $validAttribute = true;
        foreach ($definition['Conditions']['warning'] as $validatorName => $validatorOptions) {
            $validator = $this->validatorFactory->create(
                $validatorName,
                $attributeName,
                $validatorOptions
            );
            if (!$validator) {
                continue;
            }

            if (isset($definition['__original__'])) {
                $validator->setAttributeAlias($definition['__original__']);
            }
            $validator->setIdentityProvider($this->identityProvider);

            $validationResult = $validator->validate($this->attributes);

            if ($validationResult === false) {
                $validAttribute = false;
                $this->warnings[$attributeName] = $validator->getMessages();
            }
        }
        return $validAttribute;

    }

    /**
     * @param $attributeName
     * @param $definition
     * @return bool
     * @throws EngineBlock_Exception
     */
    private function validateAttributeErrors($attributeName, $definition)
    {
        if (empty($definition['Conditions']['error'])) {
            return true;
        }

        $validAttribute = true;
        foreach ($definition['Conditions']['error'] as $validatorName => $validatorOptions) {
            $validator = $this->validatorFactory->createWithExceptions(
                $validatorName,
                $attributeName,
                $validatorOptions
            );

            if (isset($definition['__original__'])) {
                $validator->setAttributeAlias($definition['__original__']);
            }
            $validator->setIdentityProvider($this->identityProvider);

            $validationResult = $validator->validate($this->attributes);

            if (!$validationResult) {
                $validAttribute = false;
                $this->errors[$attributeName] = $validator->getMessages();
            }
        }
        return $validAttribute;
    }
}
