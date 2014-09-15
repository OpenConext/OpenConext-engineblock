<?php

class EngineBlock_Attributes_Validator
{
    protected $_attributes;
    protected $_definitions;

    protected $_validAttributes = array();
    protected $_warnings = array();
    protected $_errors   = array();

    public function __construct(array $attributes)
    {
        $this->_attributes = $attributes;
    }

    public function validate()
    {
        $this->_loadAttributeDefinitions();
        $this->_denormalizeDefinitions();

        $validAttributeSet = true;
        foreach ($this->_definitions as $attributeName => $definition) {
            $validAttribute = $this->_validateAttribute($attributeName, $definition);
            if ($validAttribute) {
                $this->_validAttributes[] = $attributeName;
            }
            else {
                $validAttributeSet = false;
            }
        }

        $attributesNotInDefinitions = array_diff(array_keys($this->_attributes), array_keys($this->_definitions));
        if (!empty($attributesNotInDefinitions)) {
            foreach ($attributesNotInDefinitions as $attributeName) {
                $this->_warnings[$attributeName] = array(array(
                    'error_attribute_validator_not_in_definitions',
                    $attributeName,
                ));
            }
            $validAttributeSet = false;
        }
        return $validAttributeSet;
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
    protected function _validateAttribute($attributeName, $definition)
    {
        if (empty($definition['Conditions'])) {
            return true;
        }

        // Excludes for Situation 1, 2 and 3 (see example in docBlock)
        $isInAttributeSet = !empty($this->_attributes[$attributeName]);
        $isAnAlias = !empty($definition['__original__']);
        if (!$isInAttributeSet && $isAnAlias) {
            return true;
        }

        $validAttribute = true;
        if (!empty($definition['Conditions']['warning'])) {
            foreach ($definition['Conditions']['warning'] as $validatorName => $validatorOptions) {
                $validator = $this->_getValidator($validatorName, $attributeName, $validatorOptions, true);
                if (!$validator) {
                    continue;
                }

                if (isset($definition['__original__'])) {
                    $validator->setAttributeAlias($definition['__original__']);
                }

                $validationResult = $validator->validate($this->_attributes);

                if (!$validationResult) {
                    $validAttribute = false;
                    $this->_warnings[$attributeName] = $validator->getMessages();
                }
            }
        }

        if (!empty($definition['Conditions']['error'])) {
            foreach ($definition['Conditions']['error'] as $validatorName => $validatorOptions) {
                $validator = $this->_getValidator($validatorName, $attributeName, $validatorOptions);
                if (!$validator) {
                    continue;
                }

                if (isset($definition['__original__'])) {
                    $validator->setAttributeAlias($definition['__original__']);
                }

                $validationResult = $validator->validate($this->_attributes);

                if (!$validationResult) {
                    $validAttribute    = false;
                    $this->_errors[$attributeName] = $validator->getMessages();
                }
            }
        }
        return $validAttribute;
    }

    public function isValid($attributeName)
    {
        return in_array($attributeName, $this->_validAttributes);
    }

    public function getWarnings($attributeName = null)
    {
        if (is_null($attributeName)) {
            return $this->_warnings;
        }

        if (empty($this->_warnings[$attributeName])) {
            return array();
        }

        return $this->_warnings[$attributeName];
    }

    public function getWarningsForMissingAttributes()
    {
        $warnings = array();
        $attributesInWarning = array_keys($this->_warnings);
        foreach ($attributesInWarning as $attributeInWarning) {
            if (!isset($this->_attributes[$attributeInWarning])) {
                $warnings = array_merge($warnings, $this->_warnings[$attributeInWarning]);
            }
        }
        return $warnings;
    }

    public function getErrors($attributeName = null)
    {
        if (is_null($attributeName)) {
            return $this->_errors;
        }
        if (empty($this->_errors[$attributeName])) {
            return array();
        }
        return $this->_errors[$attributeName];
    }

    public function getErrorsForMissingAttributes()
    {
        $errors = array();
        $attributesInError = array_keys($this->_errors);
        foreach ($attributesInError as $attributeInError) {
            // Skip aliases for missing attributes
            if (!empty($this->_definitions[$attributeInError]['__original__'])) {
                continue;
            }

            if (!isset($this->_attributes[$attributeInError])) {
                $errors = array_merge($errors, $this->_errors[$attributeInError]);
            }
        }
        return $errors;
    }

    public function setDefinitions(array $definitions)
    {
        $this->_definitions = $definitions;
        return $this;
    }

    protected function _loadAttributeDefinitions()
    {
        if (isset($this->_definitions)) {
            return $this->_definitions;
        }

        $this->_definitions = json_decode(
            file_get_contents(
                EngineBlock_ApplicationSingleton::getInstance()->getConfigurationValue(
                    'attributeDefinitionFile',
                    ENGINEBLOCK_FOLDER_APPLICATION . 'configs/attributes.json'
                )
            ),
            true
        );

        return $this->_definitions;
    }

    protected function _denormalizeDefinitions()
    {
        foreach ($this->_definitions as $attributeName => $definition) {
            if (is_array($definition)) {
                continue;
            }

            $aliases = array($attributeName);
            while (!is_array($definition)) {
                $attributeName = $this->_definitions[$attributeName];

                if (empty($this->_definitions[$attributeName])) {
                    // @todo log
                    break;
                }
                $definition = $this->_definitions[$attributeName];
                $aliases[] = $attributeName;
            }

            foreach ($aliases as $alias) {
                $this->_definitions[$alias] = $definition;
                if ($attributeName !== $alias) {
                    $this->_definitions[$alias]['__original__'] = $attributeName;
                }
            }
        }
        return true;
    }

    protected function _getValidator($validatorName, $attributeName, $validatorOptions, $continueOnError = false)
    {
        $className = 'EngineBlock_Attributes_Validator_' . ucfirst($validatorName);
        if (!class_exists($className)) {
            if ($continueOnError) {
                // @todo log
                return false;
            }
            else {
                throw new EngineBlock_Exception("Unable to find $className");
            }
        }

        $validator = new $className($attributeName, $validatorOptions);

        if (!($validator instanceof EngineBlock_Attributes_Validator_Interface)) {
            if ($continueOnError) {
                // @todo log
                return false;
            }
            else {
                throw new EngineBlock_Exception("Validator $className does not match interface!");
            }
        }
        return $validator;
    }
}