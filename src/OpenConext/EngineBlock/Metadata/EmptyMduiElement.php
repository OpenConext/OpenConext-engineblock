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

namespace OpenConext\EngineBlock\Metadata;

use JsonSerializable;

/**
 * An empty mdui UIInfo element. Manage does not require all
 * mdui elements to be set for its entities. When an empty
 * element is encountered, an EmptyMduiElement can be used
 * to represent that empty value. The collection can however
 * handle these value object in a homogenous manner.
 */
class EmptyMduiElement implements MultilingualElement, JsonSerializable
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function fromJson(array $multiLingualElement): MultilingualElement
    {
        return new self($multiLingualElement['name']);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function translate(string $language): MultilingualValue
    {
        return new MultilingualValue('', $language);
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name
        ];
    }

    /**
     * Empty element, no languages can be set
     */
    public function getConfiguredLanguages(): array
    {
        return [];
    }
}
