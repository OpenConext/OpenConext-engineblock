<?php

class EngineBlock_Attributes_Validator_Factory
{
    const CLASS_BASE_DEFAULT = 'EngineBlock_Attributes_Validator_';

    /**
     * @var
     */
    private $classBase;

    public function __construct($classBase = self::CLASS_BASE_DEFAULT)
    {
        $this->classBase = $classBase;
    }

    /**
     * @param string $validatorName
     * @param string $attributeName
     * @param array $validatorOptions
     * @return null|EngineBlock_Attributes_Validator_Interface
     * @throws EngineBlock_Exception
     */
    public function create($validatorName, $attributeName, $validatorOptions)
    {
        $className =  $this->classBase . ucfirst($validatorName);

        if (!class_exists($className)) {
            return null;
        }

        $validator = new $className($attributeName, $validatorOptions);

        if (!$validator instanceof EngineBlock_Attributes_Validator_Interface) {
            return null;
        }

        return $validator;
    }

    public function createWithExceptions($validatorName, $attributeName, $validatorOptions)
    {
        $validator = $this->create($validatorName, $attributeName, $validatorOptions);

        if (!$validator) {
            throw new EngineBlock_Exception(sprintf('Unable to find validator for "%s"', $validatorName));
        }

        return $validator;
    }
}
