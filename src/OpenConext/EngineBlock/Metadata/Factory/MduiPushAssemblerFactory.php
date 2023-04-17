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

namespace OpenConext\EngineBlock\Metadata\Factory;

use OpenConext\EngineBlock\Metadata\EmptyMduiElement;
use OpenConext\EngineBlock\Metadata\Mdui;
use OpenConext\EngineBlock\Metadata\MduiElement;
use OpenConext\EngineBlock\Metadata\MultilingualElement;
use OpenConext\EngineBlock\Metadata\MultilingualValue;
use stdClass;

/**
 * Tasked with building Mdui value objects based on a
 * Manage (metadata push) JSON payload. This payload
 * is already converted to a stdClass.
 */
class MduiPushAssemblerFactory
{
    public static function buildFrom(array $properties, stdClass $connection): Mdui
    {
        $displayNameElement = self::assembleElement(
            'DisplayName',
            $properties['displayNameEn'],
            $properties['displayNameNl'],
            $properties['displayNamePt']
        );
        $descriptionElement = self::assembleElement(
            'Description',
            $properties['descriptionEn'],
            $properties['descriptionNl'],
            $properties['descriptionPt']
        );
        $keywordsElement = self::assembleElement(
            'Keywords',
            $properties['keywordsEn'],
            $properties['keywordsNl'],
            $properties['keywordsPt']
        );

        $privacyStatementUrlElement = self::assemblePrivacyStatement($connection);

        // The logo element is already assembled on the properties object
        $logoElement = new EmptyMduiElement('Logo');
        if (array_key_exists('logo', $properties)) {
            $logoElement = $properties['logo'];
        }

        return Mdui::fromMetadata(
            $displayNameElement,
            $descriptionElement,
            $keywordsElement,
            $logoElement,
            $privacyStatementUrlElement
        );
    }

    /**
     * Creates a MduiElement (or EmptyMduiElement when no appropriate data
     * is available). Consisting of MultilingualValue objects.
     */
    private static function assembleElement(
        string $elementName,
        ?string $enValue,
        ?string $nlValue,
        ?string $ptValue
    ): MultilingualElement {
        // When the main language is not set, we consider the element not to be set
        if (is_null($enValue) || $enValue === '') {
            return new EmptyMduiElement($elementName);
        }

        $enValue = new MultilingualValue($enValue, 'en');
        $nlValue = new MultilingualValue($nlValue, 'nl');
        $ptValue = new MultilingualValue($ptValue, 'pt');

        return new MduiElement($elementName, [$enValue, $nlValue, $ptValue]);
    }

    private static function assemblePrivacyStatement(stdClass $connection): MultilingualElement
    {
        $privacyStatementUrlElement = new EmptyMduiElement('PrivacyStatementURL');
        if (!empty($connection->metadata->PrivacyStatementURL)) {
            $enValue = null;
            if (!empty($connection->metadata->PrivacyStatementURL->en)) {
                $enValue = $connection->metadata->PrivacyStatementURL->en;
            }
            $nlValue = null;
            if (!empty($connection->metadata->PrivacyStatementURL->nl)) {
                $nlValue = $connection->metadata->PrivacyStatementURL->nl;
            }
            $ptValue = null;
            if (!empty($connection->metadata->PrivacyStatementURL->pt)) {
                $ptValue = $connection->metadata->PrivacyStatementURL->pt;
            }
            $privacyStatementUrlElement = self::assembleElement('PrivacyStatementURL', $enValue, $nlValue, $ptValue);
        }
        return $privacyStatementUrlElement;
    }
}
