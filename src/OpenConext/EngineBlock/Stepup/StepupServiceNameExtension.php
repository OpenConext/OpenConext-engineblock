<?php

/**
 * Copyright 2026 SURFnet B.V.
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

declare(strict_types=1);

namespace OpenConext\EngineBlock\Stepup;

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use SAML2\DOMDocumentFactory;
use SAML2\Message;
use SAML2\XML\Chunk;

final class StepupServiceNameExtension
{
    private const MDUI_NS = 'urn:oasis:names:tc:SAML:metadata:ui';

    /**
     * @param string[] $availableLocales
     */
    public static function add(
        Message $message,
        ServiceProvider $sp,
        string $locale,
        string $defaultLocale,
        array $availableLocales,
    ): void {
        if (!in_array($locale, $availableLocales, true)) {
            $locale = $defaultLocale;
        }
        $result = self::resolveName($sp, $locale);
        if ($result === null && $locale !== $defaultLocale) {
            $result = self::resolveName($sp, $defaultLocale);
        }
        if ($result === null) {
            return;
        }
        [$resolvedLocale, $name] = $result;

        $dom = DOMDocumentFactory::create();
        $uiInfo = $dom->createElementNS(self::MDUI_NS, 'mdui:UIInfo');
        $displayName = $dom->createElementNS(self::MDUI_NS, 'mdui:DisplayName');
        $displayName->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:lang', $resolvedLocale);
        $displayName->textContent = $name;
        $uiInfo->appendChild($displayName);

        $ext = $message->getExtensions();
        $ext['mdui:UIInfo'] = new Chunk($uiInfo);
        $message->setExtensions($ext);
    }

    /**
     * @return array{string, string}|null
     */
    private static function resolveName(ServiceProvider $sp, string $locale): ?array
    {
        $name = $sp->getMdui()->getDisplayNameOrNull($locale);
        if (empty($name)) {
            $name = self::localizedName($sp, $locale);
        }
        if (empty($name)) {
            return null;
        }
        return [$locale, $name];
    }

    private static function localizedName(ServiceProvider $sp, string $locale): ?string
    {
        return match ($locale) {
            'en' => $sp->nameEn,
            'nl' => $sp->nameNl,
            'pt' => $sp->namePt,
            default => null,
        };
    }
}
