<?php

/**
 * Copyright 2017 SURFnet B.V.
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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures;

use InvalidArgumentException;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\AttributeRule;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\Request;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\Response;
use OpenConext\EngineBlockBundle\AttributeAggregation\AttributeAggregationClientInterface;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore\AbstractDataStore;

final class FunctionalTestingAttributeAggregationClient implements AttributeAggregationClientInterface
{
    /**
     * @var AbstractDataStore
     */
    private $dataStore;

    public function __construct(AbstractDataStore $dataStore)
    {
        $this->dataStore = $dataStore;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function aggregate(Request $request)
    {
        $attributes = $this->dataStore->load();

        foreach ($attributes as $attribute) {
            if (empty($request->rules)) {
                throw new InvalidArgumentException(
                    "Expecting an ARP rule for {$attribute['name']}, but no rules found."
                );
            }

            if (!$this->hasRuleForAttribute($request->rules, $attribute['name'], $attribute['source'])) {
                throw new InvalidArgumentException(
                    "Expectation failed in AA client mock: expecting ARP rule for '{$attribute['name']}"
                );
            }
        }

        return Response::fromData($attributes);
    }

    /**
     * @param array $rules ARP rules
     * @param string $name
     * @param string $source
     * @return bool
     */
    private function hasRuleForAttribute(array $rules, $name, $source)
    {
        foreach ($rules as $rule) {
            if ($this->ruleMatchesAttribute($rule, $name, $source)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param AttributeRule $rule
     * @param string $name
     * @param string $source
     * @return bool
     */
    private function ruleMatchesAttribute(AttributeRule $rule, $name, $source)
    {
        return ($rule->name === $name) &&
               ($rule->source === $source);
    }

    /**
     * Configure the client mock to return no attributes.
     */
    public function returnsNothing()
    {
        $this->dataStore->save([]);
    }

    /**
     * Configure the client mock to return a speficic attribute.
     *
     * @param string $name
     * @param array $values
     * @param string $source
     */
    public function returnsAttribute($name, array $values, $source)
    {
        $attributes = $this->dataStore->load();

        $attributes[] = [
            'name'   => $name,
            'values' => $values,
            'source' => $source,
        ];

        $this->dataStore->save($attributes);
    }
}
