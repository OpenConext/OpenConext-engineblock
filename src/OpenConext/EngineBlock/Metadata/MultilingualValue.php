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

class MultilingualValue implements JsonSerializable
{
    private $language;

    private $value;

    public function __construct(?string $value, string $language)
    {
        $this->value = $value;
        $this->language = $language;
    }

    public function getValue(): string
    {
        if (is_null($this->value)) {
            return '';
        }
        return $this->value;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function jsonSerialize(): array
    {
        return [
            'value' => $this->getValue(),
            'language' => $this->language,
        ];
    }

    public function __toString(): string
    {
        return $this->getValue();
    }
}
