<?php declare(strict_types=1);
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

namespace OpenConext\EngineBlock\Metadata\Factory;

class ServiceProviderEntityinterface extends AbstractEntity
{
    public function __construct()
    {
        parent::__construct(static::class);
    }
    /**
     * This test will test if all parameters in the old mutable entity are implemented
     */
    public function test_if_all_parameters_are_implemented()
    {

        // Get all possible state from the old mutable entity
        $parameters = $this->getOrmEntityServiceProviderValues();

        // Get all state from the immutable entity adapter
        $implemented = $this->getServiceProviderValues(ServiceProviderEntityInterface::class);

        // Remove found valid parameters where the name and hinted type do match
        foreach ($parameters as $name => $type) {
            if (isset($implemented[$name]) && $implemented[$name] === $type) {
                unset($implemented[$name]);
                unset($parameters[$name]);
            }
        }

        // all parameters should be implemented as method
        $result = array_diff_key($parameters, $implemented);
        $this->assertEmpty($result, 'Missing accessor method for entity field(s): '. json_encode(array_keys($result)). ". Please provide an accessor for every field that's available on the entity.");
    }
}
