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

use OpenConext\EngineBlock\Exception\MduiNotFoundException;
use OpenConext\EngineBlock\Exception\RuntimeException;

/**
 * The Mdui value object represents the SP/IdP multilingual metadata elements
 * In case of EngineBlock we support the following mdui elements defined in the
 * SAML2 specification:
 *  - DisplayName: multilingual string
 *  - Description: multilingual string
 *  - Keywords: multilingual string
 *  - Logo: non-multilingual Logo value object
 *  - PrivacyStatementURL:  multilingual string
 *
 * For the observant reader, we do NOT store the:
 *  - InformationURL
 *  - DiscoHints
 *  - IPHint
 *  - DomainHint
 *  - GeolocationHint
 */
class Mdui
{
    private const ALLOWED_ELEMENT_NAMES = [
        'DisplayName',
        'Description',
        'Keywords',
        'Logo',
        'PrivacyStatementURL',
    ];
    const PRIMARY_LANGUAGE = 'en';

    private $values = [];

    public static function fromMetadata(
        MultilingualElement $displayName,
        MultilingualElement $description,
        MultilingualElement $keywords,
        MultilingualElement $logo,
        MultilingualElement $privacyStatementUrl
    ): Mdui {
        $values['DisplayName'] = $displayName;
        $values['Description'] = $description;
        $values['Keywords'] = $keywords;
        $values['Logo'] = $logo;
        $values['PrivacyStatementURL'] = $privacyStatementUrl;

        return new self($values);
    }

    private function __construct(array $values)
    {
        /**
         * @var string $key
         * @var MultilingualElement $value
         */
        foreach ($values as $key => $value) {
            if (!in_array($value->getName(), self::ALLOWED_ELEMENT_NAMES)) {
                throw new MduiNotFoundException(
                    sprintf(
                        'The Mdui element identified by: %s is not supported by EngineBlock. ' .
                        'The following are permitted: %s',
                        $value->getName(),
                        implode(', ', self::ALLOWED_ELEMENT_NAMES)
                    )
                );
            }

            if (!is_null($value) && in_array($key, self::ALLOWED_ELEMENT_NAMES, true)) {
                $this->values[$key] = $value;
            }
        }
    }

    public static function emptyMdui(): Mdui
    {
        return self::fromMetadata(
            new EmptyMduiElement('DisplayName'),
            new EmptyMduiElement('Description'),
            new EmptyMduiElement('Keywords'),
            new EmptyMduiElement('Logo'),
            new EmptyMduiElement('PrivacyStatementURL')
        );
    }

    public function toJson(): string
    {
        $json = json_encode($this->values);
        if (!$json) {
            throw new RuntimeException('Unable to encode Mdui values into a json string');
        }
        return $json;
    }

    public static function fromJson(string $parsedData): Mdui
    {
        $parsedData = json_decode($parsedData, true);
        $output = [];

        if ($parsedData) {
            foreach ($parsedData as $elementName => $multiLingualElement) {
                // The logo element differs from the other MduiElements, it is constructed in its own fashion
                if ($elementName === 'Logo') {
                    $output[$elementName] = Logo::fromJson($multiLingualElement);
                    continue;
                }

                // Determine if we are dealing with an empty element, mdui elements are optional.
                if (!array_key_exists('values', $multiLingualElement)) {
                    $output[$elementName] = EmptyMduiElement::fromJson($multiLingualElement);
                    continue;
                }

                $output[$elementName] = MduiElement::fromJson($multiLingualElement);
            }

            return new self($output);
        }
        // When the parsed data value is null (originating from the roles sso_provider_roles
        // table), we return an empty Mdui value object. This should be a non occurring
        // situation but could potentially happen when the Metadata is not yet pushed from
        // manage to EngineBlock
        return self::emptyMdui();
    }

    public function getDisplayName(): MultilingualElement
    {
        return $this->values['DisplayName'];
    }

    public function getDescription(): MultilingualElement
    {
        return $this->values['Description'];
    }

    public function getKeywords(): MultilingualElement
    {
        return $this->values['Keywords'];
    }

    public function getLogo(): MultilingualElement
    {
        return $this->values['Logo'];
    }

    /**
     * Get a PrivacyStatementURL translation for a given language
     * (i.e. if lang = NL, PrivacyStatementURL:nl), but if this is not available;
     * check if EN (default lang) is available and use that, only if current
     * language is not available and EN is not available, do not display anything.
     *
     * Throws an exception when an unavailable translation is requested, to prevent
     * this. Use this method in conjunction with hasPrivacyStatementURL
     */
    public function getPrivacyStatementURL(string $language): string
    {
        /** @var MultilingualElement $element */
        $element = $this->values['PrivacyStatementURL'];
        if (!$element instanceof EmptyMduiElement) {
            $primaryTranslation = $element->translate(self::PRIMARY_LANGUAGE);
            $preferredTranslation = $element->translate($language);
            // Return the requested (preferred) translation if it is available
            if (!empty($preferredTranslation->getValue())) {
                return $preferredTranslation->getValue();
            }
            // Fallback on the primary (en) language when preferred translation is not set
            return $primaryTranslation->getValue();
        }
        throw new MduiNotFoundException('The PrivacyStatementURL is not set on this entity');
    }

    /**
     * Test if a PrivacyStatementURL translation for a given language is available
     * (i.e. if lang = NL, PrivacyStatementURL:nl), but if this is not available;
     * check if the default language (EN) is available and use that, only if
     * current language is not available and EN is not available, do not display anything.
     */
    public function hasPrivacyStatementURL(string $language)
    {
        /** @var MultilingualElement $element */
        $element = $this->values['PrivacyStatementURL'];
        // No PrivacyStatement is set for any language
        if ($element instanceof EmptyMduiElement) {
            return false;
        }
        $primary = $element->translate(self::PRIMARY_LANGUAGE);
        $requested = $element->translate($language);

        return !($requested == '' && $primary == '');
    }
}
