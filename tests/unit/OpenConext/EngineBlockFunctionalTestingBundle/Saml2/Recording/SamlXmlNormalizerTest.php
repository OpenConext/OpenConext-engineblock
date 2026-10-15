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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Saml2\Recording;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SamlXmlNormalizerTest extends TestCase
{
    private const RESPONSE = <<<'XML'
<samlp:Response xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
                xmlns:xs="http://www.w3.org/2001/XMLSchema"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                ID="%1$s" InResponseTo="%2$s" Version="2.0" IssueInstant="%3$s" Destination="https://sp.example.org/acs">
    <saml:Issuer>https://engine.example.org/metadata</saml:Issuer>
    <ds:Signature>
        <ds:SignedInfo>
            <ds:Reference URI="#%1$s">
                <ds:DigestValue>%4$s</ds:DigestValue>
            </ds:Reference>
        </ds:SignedInfo>
        <ds:SignatureValue>%4$s</ds:SignatureValue>
        <ds:KeyInfo><ds:X509Data><ds:X509Certificate>MIIC
CERT</ds:X509Certificate></ds:X509Data></ds:KeyInfo>
    </ds:Signature>
    <samlp:Status><samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success"/></samlp:Status>
    <saml:Assertion ID="%5$s" IssueInstant="%3$s" Version="2.0">
        <saml:AttributeStatement>
            <saml:Attribute Name="urn:mace:dir:attribute-def:uid">
                <saml:AttributeValue xsi:type="xs:string">jdoe</saml:AttributeValue>
            </saml:Attribute>
        </saml:AttributeStatement>
    </saml:Assertion>
</samlp:Response>
XML;

    public function testIgnoresNamespacePrefixesAndAttributeOrder(): void
    {
        $original = $this->response('_aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'EB1111111111111111111111111111111111111111');
        $reprefixed = str_replace(
            ['samlp:', 'xmlns:samlp', 'saml:', 'xmlns:saml=', 'xs:', 'xmlns:xs'],
            ['p:', 'xmlns:p', 'a:', 'xmlns:a=', 'xsd:', 'xmlns:xsd'],
            $original
        );
        $reordered = str_replace(
            'Version="2.0" IssueInstant="2026-01-01T10:00:00Z" Destination="https://sp.example.org/acs"',
            'Destination="https://sp.example.org/acs" IssueInstant="2026-01-01T10:00:00Z" Version="2.0"',
            $reprefixed
        );

        $this->assertSame($this->normalize($original), $this->normalize($reordered));
    }

    public function testMasksVolatileValuesButKeepsReferencesVisible(): void
    {
        $first = $this->normalize(
            $this->response('_aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'EB1111111111111111111111111111111111111111')
        );
        $second = $this->normalize(
            $this->response(
                '_bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb',
                'EB2222222222222222222222222222222222222222',
                '2027-05-05T05:05:05Z',
                'different'
            )
        );

        $this->assertSame($first, $second);
        $this->assertStringContainsString('@ID: ID-1', $first);
        $this->assertStringContainsString('@InResponseTo: ID-2', $first);
        $this->assertStringContainsString('@URI: #ID-1', $first);
        $this->assertStringContainsString('@IssueInstant: <timestamp>', $first);
        $this->assertStringContainsString("ds:SignatureValue: <masked>", $first);
        $this->assertStringContainsString('ds:X509Certificate: MIICCERT', $first);
    }

    public function testNormalizesXsiTypeIndependentlyOfPrefix(): void
    {
        $dump = $this->normalize(
            str_replace(['xs:', 'xmlns:xs'], ['xsd:', 'xmlns:xsd'], $this->response('_' . str_repeat('a', 40), 'EB' . str_repeat('1', 40)))
        );

        $this->assertStringContainsString('@xsi:type: xs:string', $dump);
    }

    public function testReportsSemanticDifferences(): void
    {
        $original = $this->response('_' . str_repeat('a', 40), 'EB' . str_repeat('1', 40));
        $changed = str_replace(
            'urn:oasis:names:tc:SAML:2.0:status:Success',
            'urn:oasis:names:tc:SAML:2.0:status:Requester',
            $original
        );

        $this->assertNotSame($this->normalize($original), $this->normalize($changed));
    }

    public function testNumbersIdentifiersAcrossMessagesOfOneInstance(): void
    {
        $normalizer = new SamlXmlNormalizer();
        $request = '<samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" ID="' . 'EB' . str_repeat('1', 40) . '"/>';

        $normalizer->normalize($request);
        $response = $normalizer->normalize(
            $this->response('_' . str_repeat('a', 40), 'EB' . str_repeat('1', 40))
        );

        $this->assertStringContainsString('@InResponseTo: ID-1', $response);
        $this->assertStringContainsString('@ID: ID-2', $response);
    }

    public function testMasksIdentifiersWithEngineBlockAndMockPrefixes(): void
    {
        $dump = $this->normalize(
            '<samlp:Response xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"'
            . ' ID="CORTO26399c78ddb00a5f6717529a0c4d496672bfc800" InResponseTo="OPENCONEXT_ETS_48213"'
            . ' Destination="https://sp.example.org/acs"/>'
        );

        $this->assertStringContainsString('@ID: ID-1', $dump);
        $this->assertStringContainsString('@InResponseTo: ID-2', $dump);
        $this->assertStringContainsString('@Destination: https://sp.example.org/acs', $dump);
    }

    public function testMasksReferenceAttributesRegardlessOfIdentifierFormat(): void
    {
        $dump = $this->normalize(
            '<samlp:Response xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"'
            . ' ID="a1" InResponseTo="6ace2fbaeaeaf" Destination="https://sp.example.org/acs"/>'
        );

        $this->assertStringContainsString('@ID: ID-1', $dump);
        $this->assertStringContainsString('@InResponseTo: ID-2', $dump);
        $this->assertStringContainsString('@Destination: https://sp.example.org/acs', $dump);
    }

    public function testKeepsReferenceBetweenRequestAndResponse(): void
    {
        $normalizer = new SamlXmlNormalizer();
        $normalizer->normalize('<samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" ID="req"/>');
        $dump = $normalizer->normalize(
            '<samlp:Response xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" ID="res" InResponseTo="req"/>'
        );

        $this->assertStringContainsString('@ID: ID-2', $dump);
        $this->assertStringContainsString('@InResponseTo: ID-1', $dump);
    }

    public function testMasksTimeBasedMockIdentityProviderFixtures(): void
    {
        $dump = $this->normalize(
            '<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">'
            . '<saml:NameID>ETS-MOCK-IDP-1791897603</saml:NameID>'
            . '<saml:AttributeValue>urn:collab:person:example.org:test179189752994056</saml:AttributeValue>'
            . '</saml:Assertion>'
        );

        $this->assertStringContainsString('saml:NameID: ID-1', $dump);
        $this->assertStringContainsString('saml:AttributeValue: urn:collab:person:example.org:ID-2', $dump);
    }

    public function testRejectsUnparsableXml(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new SamlXmlNormalizer())->normalize('<not-closed');
    }

    private function normalize(string $xml): string
    {
        return (new SamlXmlNormalizer())->normalize($xml);
    }

    private function response(
        string $id,
        string $inResponseTo,
        string $instant = '2026-01-01T10:00:00Z',
        string $digest = 'c2lnbmF0dXJl',
    ): string {
        return sprintf(self::RESPONSE, $id, $inResponseTo, $instant, $digest, '_' . str_repeat('c', 40));
    }
}
