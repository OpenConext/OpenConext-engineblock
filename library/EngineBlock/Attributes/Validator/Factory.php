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
