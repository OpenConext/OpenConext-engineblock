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

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use InvalidArgumentException;

final class SamlXmlNormalizer
{
    private const XMLNS = 'http://www.w3.org/2000/xmlns/';

    private const NAMESPACE_PREFIXES = [
        'urn:oasis:names:tc:SAML:2.0:protocol' => 'samlp',
        'urn:oasis:names:tc:SAML:2.0:assertion' => 'saml',
        'urn:oasis:names:tc:SAML:2.0:metadata' => 'md',
        'urn:oasis:names:tc:SAML:metadata:ui' => 'mdui',
        'urn:oasis:names:tc:SAML:metadata:attribute' => 'mdattr',
        'urn:oasis:names:tc:SAML:profiles:SSO:idp-discovery-protocol' => 'idpdisc',
        'urn:mace:shibboleth:metadata:1.0' => 'shibmd',
        'http://www.w3.org/2000/09/xmldsig#' => 'ds',
        'http://www.w3.org/2001/04/xmlenc#' => 'xenc',
        'http://www.w3.org/2001/XMLSchema' => 'xs',
        'http://www.w3.org/2001/XMLSchema-instance' => 'xsi',
        'http://www.w3.org/XML/1998/namespace' => 'xml',
    ];

    private const MASKED_ELEMENTS = [
        'http://www.w3.org/2000/09/xmldsig#' => ['SignatureValue', 'DigestValue'],
        'http://www.w3.org/2001/04/xmlenc#' => ['CipherValue'],
    ];

    private const REFERENCE_ATTRIBUTES = ['ID', 'InResponseTo'];

    private const IDENTIFIER_PATTERN =
        '/(?<![A-Za-z0-9])(?:[A-Za-z]*_?[0-9a-fA-F]{32,}|OPENCONEXT_ETS_\d+|ETS-MOCK-IDP-\d+|test\d{15})'
        . '(?![A-Za-z0-9])/';
    private const UUID_PATTERN =
        '/(?<![A-Za-z0-9])[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}(?![A-Za-z0-9])/';
    private const TIMESTAMP_PATTERN =
        '/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?(Z|[+-]\d{2}:\d{2})/';

    // Numbered in order of first appearance so references between messages of one scenario stay visible.
    // Use one instance per scenario.
    /** @var array<string, string> */
    private array $identifiers = [];

    public function normalize(string $xml): string
    {
        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $loaded = $document->loadXML($xml);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded || $document->documentElement === null) {
            throw new InvalidArgumentException(
                'Unable to parse SAML message: ' . ($errors[0]->message ?? 'unknown error')
            );
        }

        $lines = [];
        $this->dumpElement($document->documentElement, 0, $lines);

        return implode("\n", $lines) . "\n";
    }

    /**
     * @param string[] $lines
     */
    private function dumpElement(DOMElement $element, int $depth, array &$lines): void
    {
        $indent = str_repeat('  ', $depth);
        $name = $this->qualifiedName($element->namespaceURI, $element->localName);
        $attributes = $this->attributesOf($element);
        $childElements = $this->childElementsOf($element);
        $text = $childElements === [] ? $this->textOf($element) : '';

        if ($attributes === [] && $childElements === []) {
            $lines[] = $text === '' ? $indent . $name : $indent . $name . ': ' . $text;
            return;
        }

        $lines[] = $indent . $name;
        foreach ($attributes as $attributeName => $value) {
            $lines[] = $indent . '  @' . $attributeName . ': ' . $value;
        }
        if ($text !== '') {
            $lines[] = $indent . '  #text: ' . $text;
        }
        foreach ($childElements as $child) {
            $this->dumpElement($child, $depth + 1, $lines);
        }
    }

    /**
     * @return array<string, string>
     */
    private function attributesOf(DOMElement $element): array
    {
        $attributes = [];
        foreach ($element->attributes as $attribute) {
            if ($attribute->namespaceURI === self::XMLNS) {
                continue;
            }
            $attributeName = $this->qualifiedName($attribute->namespaceURI, $attribute->localName);
            $attributes[$attributeName] = match (true) {
                $attributeName === 'xsi:type' => $this->normalizeQName($element, $attribute->value),
                in_array($attributeName, self::REFERENCE_ATTRIBUTES, true) => $this->identifier($attribute->value),
                default => $this->maskVolatile($attribute->value),
            };
        }
        ksort($attributes);

        return $attributes;
    }

    private function identifier(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        return $this->identifiers[$value] ??= 'ID-' . (count($this->identifiers) + 1);
    }

    /**
     * @return DOMElement[]
     */
    private function childElementsOf(DOMElement $element): array
    {
        $children = [];
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $children[] = $child;
            }
        }

        return $children;
    }

    private function textOf(DOMElement $element): string
    {
        $text = '';
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMText) {
                $text .= $child->nodeValue;
            }
        }
        $text = trim($text);

        if ($this->isMasked($element)) {
            return $text === '' ? '' : '<masked>';
        }

        if ($this->isCertificate($element)) {
            return preg_replace('/\s+/', '', $text) ?? $text;
        }

        return $this->maskVolatile($text);
    }

    private function qualifiedName(?string $namespace, string $localName): string
    {
        if ($namespace === null || $namespace === '') {
            return $localName;
        }

        $prefix = self::NAMESPACE_PREFIXES[$namespace] ?? '{' . $namespace . '}';

        return str_starts_with($prefix, '{') ? $prefix . $localName : $prefix . ':' . $localName;
    }

    private function normalizeQName(DOMNode $context, string $value): string
    {
        if (!str_contains($value, ':')) {
            return $value;
        }

        [$prefix, $localName] = explode(':', $value, 2);
        $namespace = $context->lookupNamespaceURI($prefix);

        return $namespace === null ? $value : $this->qualifiedName($namespace, $localName);
    }

    private function isMasked(DOMElement $element): bool
    {
        return in_array($element->localName, self::MASKED_ELEMENTS[$element->namespaceURI ?? ''] ?? [], true);
    }

    private function isCertificate(DOMElement $element): bool
    {
        return $element->localName === 'X509Certificate'
            && $element->namespaceURI === 'http://www.w3.org/2000/09/xmldsig#';
    }

    private function maskVolatile(string $value): string
    {
        $value = preg_replace_callback(
            self::TIMESTAMP_PATTERN,
            static fn(array $match): string => sprintf(
                '<timestamp%s%s>',
                ($match[1] ?? '') !== '' ? '+fraction' : '',
                $match[2] === 'Z' ? '' : '+offset'
            ),
            $value
        ) ?? $value;

        foreach ([self::IDENTIFIER_PATTERN, self::UUID_PATTERN] as $pattern) {
            $value = preg_replace_callback(
                $pattern,
                fn(array $match): string => $this->identifier($match[0]),
                $value
            ) ?? $value;
        }

        return $value;
    }
}
