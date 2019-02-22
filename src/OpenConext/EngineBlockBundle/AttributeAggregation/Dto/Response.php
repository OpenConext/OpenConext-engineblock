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

use OpenConext\EngineBlockBundle\Exception\InvalidAttributeAggregationResponseException;

final class Response
{
    /**
     * @var AggregatedAttribute[]
     */
    public $attributes = [];

    /**
     * @param mixed $jsonData
     * @return Response
     */
    public static function fromData($jsonData)
    {
        if (!is_array($jsonData)) {
            throw new InvalidAttributeAggregationResponseException('No list of attributes was found in the aggregator response');
        }

        $attributes = [];

        foreach ($jsonData as $attributeData) {
            $attributes[] = self::parseAggregatedAttribute($attributeData);
        }

        $response = new self;
        $response->attributes = $attributes;

        return $response;
    }

    /**
     * @param array $jsonData
     * @return AggregatedAttribute
     */
    private static function parseAggregatedAttribute(array $attributeData)
    {
        if (!isset($attributeData['name'])) {
            throw new InvalidAttributeAggregationResponseException('Missing aggregated attribute name');
        }

        if (!isset($attributeData['values'])) {
            throw new InvalidAttributeAggregationResponseException('Missing aggregated attribute value');
        }

        if (!isset($attributeData['source'])) {
            throw new InvalidAttributeAggregationResponseException('Missing aggregated attribute source');
        }

        return AggregatedAttribute::from(
            (string) $attributeData['name'],
            (array) $attributeData['values'],
            (string) $attributeData['source']
        );
    }
}
