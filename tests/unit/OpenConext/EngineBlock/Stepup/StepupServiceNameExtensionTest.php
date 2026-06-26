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

use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Mdui;
use PHPUnit\Framework\TestCase;
use SAML2\AuthnRequest;
use SAML2\XML\Chunk;

class StepupServiceNameExtensionTest extends TestCase
{
    public function testAddsDisplayNameForRequestedLocale(): void
    {
        $sp = $this->spWithNames('My Service EN', 'Mijn Service NL');
        $request = new AuthnRequest();

        StepupServiceNameExtension::add($request, $sp, 'nl');

        $nodes = $this->queryXpath($request, './/mdui:DisplayName[@xml:lang="nl"]');
        $this->assertSame(1, $nodes->length);
        $this->assertSame('Mijn Service NL', $nodes->item(0)->textContent);
    }

    public function testFallsBackToEnglishWhenRequestedLocaleHasNoName(): void
    {
        $sp = $this->spWithNames('My Service EN');
        $request = new AuthnRequest();

        StepupServiceNameExtension::add($request, $sp, 'nl');

        $nodes = $this->queryXpath($request, './/mdui:DisplayName[@xml:lang="en"]');
        $this->assertSame(1, $nodes->length);
        $this->assertSame('My Service EN', $nodes->item(0)->textContent);
    }

    public function testAddsNoExtensionWhenNeitherLocaleNorEnglishHasName(): void
    {
        $sp = $this->spWithNames();
        $request = new AuthnRequest();

        StepupServiceNameExtension::add($request, $sp, 'nl');

        $ext = $request->getExtensions();
        $this->assertArrayNotHasKey('mdui:UIInfo', $ext);
    }

    public function testMduiDisplayNameTakesPrecedenceOverFlatField(): void
    {
        $sp = $this->spWithMduiDisplayName('en', 'Mdui Service Name');
        $sp->nameEn = 'Flat Field Name';
        $request = new AuthnRequest();

        StepupServiceNameExtension::add($request, $sp, 'en');

        $nodes = $this->queryXpath($request, './/mdui:DisplayName[@xml:lang="en"]');
        $this->assertSame(1, $nodes->length);
        $this->assertSame('Mdui Service Name', $nodes->item(0)->textContent);
    }

    public function testLocaleTagMatchesActualContentLocale(): void
    {
        $sp = $this->spWithNames('Only English Name');
        $request = new AuthnRequest();

        StepupServiceNameExtension::add($request, $sp, 'nl');

        $nlNodes = $this->queryXpath($request, './/mdui:DisplayName[@xml:lang="nl"]');
        $enNodes = $this->queryXpath($request, './/mdui:DisplayName[@xml:lang="en"]');
        $this->assertSame(0, $nlNodes->length, 'Should not add nl tag when using en fallback');
        $this->assertSame(1, $enNodes->length, 'Should add en tag matching the actual content locale');
    }

    public function testFallsBackToFlatFieldWhenMduiDisplayNameIsEmpty(): void
    {
        $mduiJson = '{"DisplayName":{"name":"DisplayName","values":{"en":{"value":"","language":"en"}}},'
            . '"Description":{"name":"Description"},"Keywords":{"name":"Keywords"},'
            . '"Logo":{"name":"Logo"},"PrivacyStatementURL":{"name":"PrivacyStatementURL"}}';
        $sp = new ServiceProvider('https://sp.example.org', Mdui::fromJson($mduiJson));
        $sp->nameEn = 'Flat Field Name';
        $request = new AuthnRequest();

        StepupServiceNameExtension::add($request, $sp, 'en');

        $nodes = $this->queryXpath($request, './/mdui:DisplayName[@xml:lang="en"]');
        $this->assertSame(1, $nodes->length);
        $this->assertSame('Flat Field Name', $nodes->item(0)->textContent);
    }

    public function testPreservesExistingExtensions(): void
    {
        $sp = $this->spWithNames('My Service');
        $request = new AuthnRequest();
        $request->setExtensions(['existing:Extension' => new Chunk(
            (new DOMDocument())->createElement('existing:Extension')
        )]);

        StepupServiceNameExtension::add($request, $sp, 'en');

        $ext = $request->getExtensions();
        $this->assertArrayHasKey('existing:Extension', $ext);
        $this->assertArrayHasKey('mdui:UIInfo', $ext);
    }

    private function spWithNames(string $nameEn = '', string $nameNl = '', string $namePt = ''): ServiceProvider
    {
        $sp = new ServiceProvider('https://sp.example.org');
        $sp->nameEn = $nameEn;
        $sp->nameNl = $nameNl;
        $sp->namePt = $namePt;
        return $sp;
    }

    private function spWithMduiDisplayName(string $locale, string $displayName): ServiceProvider
    {
        $mduiJson = sprintf(
            '{"DisplayName":{"name":"DisplayName","values":{"%s":{"value":"%s","language":"%s"}}},'
            . '"Description":{"name":"Description"},"Keywords":{"name":"Keywords"},'
            . '"Logo":{"name":"Logo"},"PrivacyStatementURL":{"name":"PrivacyStatementURL"}}',
            $locale,
            $displayName,
            $locale
        );
        return new ServiceProvider('https://sp.example.org', Mdui::fromJson($mduiJson));
    }

    private function getExtensionElement(AuthnRequest $request): DOMElement
    {
        $ext = $request->getExtensions();
        $this->assertArrayHasKey('mdui:UIInfo', $ext);
        $chunk = $ext['mdui:UIInfo'];
        $this->assertInstanceOf(Chunk::class, $chunk);
        return $chunk->getXML();
    }

    private function queryXpath(AuthnRequest $request, string $expression): DOMNodeList
    {
        $el = $this->getExtensionElement($request);
        $xpath = new DOMXPath($el->ownerDocument);
        $xpath->registerNamespace('mdui', 'urn:oasis:names:tc:SAML:metadata:ui');
        $xpath->registerNamespace('xml', 'http://www.w3.org/XML/1998/namespace');
        return $xpath->query($expression, $el);
    }
}
