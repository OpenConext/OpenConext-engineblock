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

namespace OpenConext\EngineBlockBundle\AttributeAggregation\Dto;

use OpenConext\EngineBlock\Assert\Assertion;

final class AggregatedAttribute
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string[]
     */
    public $values = [];

    /**
     * @var string
     */
    public $source;

    /**
     * @param string $name
     * @param string[] $values
     * @param string $source
     */
    public static function from($name, array $values, $source)
    {
        Assertion::string($name, 'Attribute name must be a string, received "%s" (%s)');
        Assertion::string($source, 'Attribute source must be a string, received "%s" (%s)');

        $attribute = new self;
        $attribute->name = $name;
        $attribute->values = $values;
        $attribute->source = $source;

        return $attribute;
    }
}
