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

namespace OpenConext\EngineBlock\Metadata\Factory\Factory;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

class AbstractFactoryTest extends TestCase
{

    protected function getParameters($className, $skipParameters = [])
    {
        $results = [];
        $class = new ReflectionClass($className);
        $properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($properties as $property) {
            if (!$property->isStatic() && !in_array($property->getName(), $skipParameters)) {
                preg_match('/@var (.*)\n/', $property->getDocComment(), $matches);
                $results[$property->getName()] = $matches[1];
            }
        }

        return $results;
    }

    protected function getMethods($className, $skipMethods = [])
    {
        $results = [];
        $class = new ReflectionClass($className);
        $methods = $class->getMethods(ReflectionProperty::IS_PUBLIC);
        foreach ($methods as $method) {
            if (!$method->isStatic() && !in_array($method->getName(), $skipMethods)) {
                preg_match('/@return (.*)\n/', $method->getDocComment(), $matches);
                $results[$method->getName()] = $matches[1];
            }
        }
        return $results;
    }

    protected function getGetterBaseNameFromMethodNames(array $methodNames)
    {
        $results = [];
        foreach ($methodNames as $name => $type) {
            if ($name == '__construct') {
                continue;
            }

            if (substr($name, 0, 3) == 'get') {
                $name = lcfirst(substr($name, 3));
            } else if (substr($name, 0, 2) == 'is') {
                $name = lcfirst(substr($name, 2));
            } else {
                throw new \Exception('INVALID: '. $name);
            }

            $results[$name] = $type;
        }

        return $results;
    }
}
