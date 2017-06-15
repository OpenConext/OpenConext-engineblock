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

namespace OpenConext\EngineBlockBundle\AttributeAggregation\Dto;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Component\EngineBlockMetadata\AttributeReleasePolicy;

final class AttributeRule
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $value;

    /**
     * @var string
     */
    public $source;

    /**
     * @param string $name
     * @param string $value
     * @param string $source
     * @return AttributeRule
     */
    public static function from($name, $value, $source)
    {
        Assertion::string($name, 'Attribute name must be a string, received "%s" (%s)');
        Assertion::string($value, 'Attribute value must be a string, received "%s" (%s)');
        Assertion::string($source, 'Attribute source must be a string, received "%s" (%s)');

        $rule = new self;
        $rule->name = $name;
        $rule->value = $value;
        $rule->source = $source;

        return $rule;
    }

    /**
     * @param AttributeReleasePolicy $arp
     * @return AttributeRule[]
     */
    public static function fromArp(AttributeReleasePolicy $arp)
    {
        $queries = [];
        foreach ($arp->getRulesWithSourceSpecification() as $name => $rules) {
            foreach ($rules as $rule) {
                $queries[] = self::from($name, $rule['value'], $rule['source']);
            }
        }
        return $queries;
    }
}
