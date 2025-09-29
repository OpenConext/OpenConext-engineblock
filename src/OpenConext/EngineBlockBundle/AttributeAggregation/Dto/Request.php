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

namespace OpenConext\EngineBlockBundle\AttributeAggregation\Dto;

use JsonSerializable;
use OpenConext\EngineBlock\Assert\Assertion;

final class Request implements JsonSerializable
{
    /**
     * @var string
     */
    public $spEntityId;

    /**
     * @var string
     */
    public $idpEntityId;

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
     * @param string $spEntityId
     * @param string $subjectId
     * @param array $attributes
     * @param AttributeRule[] $rules
     * @return Request $request
     */
    public static function from($spEntityId, $idpEntityId, $subjectId, array $attributes, array $rules)
    {
        Assertion::string($spEntityId, 'The SP entity ID must be a string, received "%s" (%s)');
        Assertion::string($idpEntityId, 'The IDP entity ID must be a string, received "%s" (%s)');
        Assertion::string($subjectId, 'The SubjectId must be a string, received "%s" (%s)');
        Assertion::allIsInstanceOf($rules, AttributeRule::class, 'All attributes must be of type AttributeRule');

        // Filter the non string valued attributes
        $attributes = self::filterNonStringValuesFromAttributes($attributes);

        $request = new self;
        $request->spEntityId = $spEntityId;
        $request->idpEntityId = $idpEntityId;
        $request->subjectId = $subjectId;
        $request->attributes = $attributes;
        $request->rules = $rules;

        return $request;
    }

    private static function filterNonStringValuesFromAttributes($attributes)
    {
        return array_filter($attributes, function ($attributeValues) {
            foreach ($attributeValues as $attributeValue) {
                if (!is_string($attributeValue)) {
                    return false;
                }
            }
            return true;
        });
    }

    public function jsonSerialize(): mixed
    {
        return [
            'userAttributes' => array_merge(
                [
                    [
                        'name' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
                        'values' => [$this->subjectId],
                    ],
                    [
                        'name' => 'SPentityID',
                        'values' => [$this->spEntityId],
                    ],
                    [
                        'name' => 'IDPentityID',
                        'values' => [$this->idpEntityId],
                    ]
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
