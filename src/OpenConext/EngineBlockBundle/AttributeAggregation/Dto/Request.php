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

use JsonSerializable;
use OpenConext\EngineBlock\Assert\Assertion;

final class Request implements JsonSerializable
{
    /**
     * @var string
     */
    public $subjectId;

    /**
     * Attributes as sent by the IdP: name => values.
     *
     * @var array
     */
    public $attributes = [];

    /**
     * @var AttributeRule[]
     */
    public $rules = [];

    /**
     * @param string $subjectId
     * @param AttributeRule[] $rules
     * @return Request $request
     */
    public static function from($subjectId, array $attributes, array $rules)
    {
        Assertion::string($subjectId, 'The SubjectId must be a string, received "%s" (%s)');
        Assertion::allIsInstanceOf($rules, AttributeRule::class, 'All attributes must be of type AttributeRule');

        $request = new self;
        $request->subjectId = $subjectId;
        $request->attributes = $attributes;
        $request->rules = $rules;

        return $request;
    }

    public function jsonSerialize()
    {
        return [
            'userAttributes' => array_merge(
                [
                    [
                        'name' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
                        'values' => [$this->subjectId],
                    ],
                ],
                array_map(
                    function ($values, $name) {
                        return [
                            'name' => $name,
                            'values' => $values,
                        ];
                    },
                    $this->attributes,
                    array_keys($this->attributes)
                )
            ),
            'arpAttributes' => $this->getAttributeRulesByName(),
        ];
    }

    /**
     * Create a list of values and sources grouped by attribute name.
     *
     * @return array
     */
    private function getAttributeRulesByName()
    {
        $attributes = [];
        foreach ($this->rules as $rule) {
            $attributes[$rule->name][] = [
                'value' => $rule->value,
                'source' => $rule->source,
            ];
        }
        return $attributes;
    }
}
