<?php declare(strict_types=1);

/**
 * Copyright 2025 SURFnet B.V.
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
use OpenConext\EngineBlock\Exception\InvalidDiscoveryException;

/**
 * Value object representing the cosmetic override data when a 'sub-idp' is present
 */
class Discovery implements JsonSerializable
{
    /**
     * @var string[]
     */
    private $names;

    /**
     * @var string[]
     */
    private $keywords;

    /**
     * @var ?Logo
     */
    private $logo;

    /**
     * @param array<string,string> $names
     * @param array<string,string> $keywords
     */
    public static function create(array $names, array $keywords, ?Logo $logo): Discovery
    {
        $discovery = new self;
        $discovery->logo = $logo;

        self::assertLocaleValueArray($names);
        self::assertLocaleValueArray($keywords);

        $discovery->names = array_filter($names);
        $discovery->keywords = array_filter($keywords);

        if (!$discovery->isValid()) {
            throw new InvalidDiscoveryException('The Discovery does not have a required english name.');
        }

        return $discovery;
    }

    private static function assertLocaleValueArray(array $array): void
    {
        foreach ($array as $localeKey => $value) {
            if (!is_string($localeKey)) {
                throw new InvalidDiscoveryException(sprintf("Discovery language key must be a string, '%s' given", $localeKey));
            }

            if (strlen($localeKey) !== 2) {
                throw new InvalidDiscoveryException(sprintf("Invalid discovery language key, '%s' given", $localeKey));
            }


            if (!is_string($value)) {
                throw new InvalidDiscoveryException(sprintf("Discovery value must be a string, '%s' given", $value));
            }
        }
    }

    public function jsonSerialize()
    {
        return [
            'names' => $this->names,
            'keywords' => $this->keywords,
            'logo' => $this->logo,
        ];
    }

    public function hasLogo(): bool
    {
        return $this->logo !== null && $this->logo->url !== null;
    }

    public function getLogo(): ?Logo
    {
        return $this->logo;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getName(string $locale): string
    {
        if ($locale !== '' && isset($this->names[$locale])) {
            return $this->names[$locale];
        }

        return $this->names['en'] ?? '';
    }

    public function getKeywords(string $locale): string
    {
        if ($locale !== '' && isset($this->keywords[$locale])) {
            return $this->keywords[$locale];
        }

        return $this->keywords['en'] ?? '';
    }

    /**
     * @return string[]
     */
    public function getKeywordsArray(string $locale): array
    {
        return explode(' ', $this->getKeywords($locale));
    }

    public function isValid(): bool
    {
        return $this->getName('en') !== '';
    }
}
