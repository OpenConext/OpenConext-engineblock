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
use OpenConext\EngineBlock\Exception\MduiRuntimeException;

/**
 * A SAML2 metadata logo.
 * @package OpenConext\EngineBlock\Metadata
 */
class Logo implements MultilingualElement, JsonSerializable
{
    public string $url;
    public ?int $height;
    public ?int $width;

    public function __construct(string $url, ?int $height = null, ?int $width = null)
    {
        $this->url = $url;
        $this->height = $height;
        $this->width = $width;
    }

    public static function fromJson(array $multiLingualElement): MultilingualElement
    {
        if (!array_key_exists('url', $multiLingualElement)) {
            throw new MduiRuntimeException(
                'Incomplete MDUI Logo data. The URL is missing while serializing the data from JSON'
            );
        }
        $element = new self($multiLingualElement['url']);
        if (array_key_exists('height', $multiLingualElement)) {
            $element->height = $multiLingualElement['height'];
        }
        if (array_key_exists('width', $multiLingualElement)) {
            $element->width = $multiLingualElement['width'];
        }
        return $element;
    }

    public function getName(): string
    {
        return 'Logo';
    }

    public function translate(string $language): MultilingualValue
    {
        throw new MduiRuntimeException('We do not implement the Mdui Logo in a multilingual fashion');
    }

    public function jsonSerialize(): mixed
    {
        return [
            'name' => $this->getName(),
            'url' => $this->url,
            'width' => $this->width,
            'height' => $this->height,
        ];
    }

    /**
     * Always consider the Logo to be of the primary language
     */
    public function getConfiguredLanguages(): array
    {
        return [self::PRIMARY_LANGUAGE];
    }

    /**
     * A convenience static constructor for the Logo.
     */
    public static function fromArray(array $logo): Logo
    {
        return new self($logo["url"], $logo["height"], $logo["width"]);
    }
}
