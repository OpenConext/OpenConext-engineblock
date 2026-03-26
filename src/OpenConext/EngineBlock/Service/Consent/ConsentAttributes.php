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

namespace OpenConext\EngineBlock\Service\Consent;

use SAML2\XML\saml\NameID;
use function array_filter;
use function array_keys;
use function array_values;
use function count;
use function implode;
use function is_array;
use function is_string;
use function ksort;
use function mb_strtolower;
use function serialize;
use function sort;

/**
 * A normalised, immutable representation of the SAML attributes used to compute a stable consent hash.
 *
 * Use the named constructors to select the comparison strategy:
 *   - ConsentAttributes::withValues()  — hash covers attribute names AND values (consent_store_values=true)
 *   - ConsentAttributes::namesOnly()   — hash covers attribute names only       (consent_store_values=false)
 *
 * All normalisation (NameID flattening, case folding, empty-value stripping, recursive sorting)
 * is applied inside this class so callers deal only with the resulting compare value.
 */
final class ConsentAttributes
{
    private string $compareValue;

    private function __construct(string $compareValue)
    {
        $this->compareValue = $compareValue;
    }

    /**
     * Build a ConsentAttributes where the compare value includes both attribute names and values.
     * Use when consent_store_values=true.
     */
    public static function withValues(array $raw): self
    {
        $normalised = self::normalise($raw);
        return new self(serialize(self::sortRecursive(self::removeEmptyAttributes($normalised))));
    }

    /**
     * Build a ConsentAttributes where the compare value includes attribute names only.
     * Use when consent_store_values=false.
     */
    public static function namesOnly(array $raw): self
    {
        $normalised = self::normalise($raw);
        $sortedKeys = self::sortRecursive(array_keys(self::removeEmptyAttributes($normalised)));
        return new self(implode('|', $sortedKeys));
    }

    public function getCompareValue(): string
    {
        return $this->compareValue;
    }

    /**
     * Applies all normalisation steps shared by both strategies.
     */
    private static function normalise(array $raw): array
    {
        return self::caseNormalizeStringArray(self::nameIdNormalize($raw));
    }

    /**
     * Converts NameID objects to a plain array so they survive serialisation and case-folding.
     */
    private static function nameIdNormalize(array $attributes): array
    {
        array_walk_recursive($attributes, function (&$value) {
            if ($value instanceof NameID) {
                $value = ['value' => $value->getValue(), 'Format' => $value->getFormat()];
            }
        });
        return $attributes;
    }

    /**
     * Lowercases all array keys and string values recursively using mb_strtolower
     * to handle multi-byte UTF-8 characters (e.g. Ü→ü, Arabic, Chinese — common in SAML).
     *
     * The previous implementation used serialize/strtolower/unserialize which corrupted
     * PHP's s:N: byte-length markers for multi-byte values, causing unserialize() to silently
     * return false and producing wrong hashes for any user with a non-ASCII attribute value.
     */
    private static function caseNormalizeStringArray(array $original): array
    {
        $result = [];
        foreach ($original as $key => $value) {
            $normalizedKey = is_string($key) ? mb_strtolower($key) : $key;
            if (is_array($value)) {
                $result[$normalizedKey] = self::caseNormalizeStringArray($value);
            } elseif (is_string($value)) {
                $result[$normalizedKey] = mb_strtolower($value);
            } else {
                $result[$normalizedKey] = $value;
            }
        }
        return $result;
    }

    /**
     * Strips null, empty-string, and empty-array values recursively so that stray
     * empty entries do not produce spurious re-consent, while a fully-empty attribute
     * (key present but no values) is removed entirely and DOES trigger re-consent.
     */
    private static function removeEmptyAttributes(array $array): array
    {
        foreach ($array as $key => $value) {
            if ($value === null || $value === '' || $value === []) {
                unset($array[$key]);
            } elseif (is_array($value)) {
                $array[$key] = self::removeEmptyAttributes($value);
            }
        }
        return $array;
    }

    /**
     * Sorts arrays recursively: associative arrays by key (ksort), sequential arrays by value (sort).
     * Re-indexes sequential arrays first to erase any sparse numeric keys.
     */
    private static function sortRecursive(array $sortMe): array
    {
        $copy = $sortMe;
        $sortFunction = 'ksort';

        if (self::isSequentialArray($copy)) {
            $sortFunction = 'sort';
            $copy = array_values($copy);
        }

        $sortFunction($copy);
        foreach ($copy as $key => $value) {
            if (is_array($value)) {
                $copy[$key] = self::sortRecursive($value);
            }
        }

        return $copy;
    }

    private static function isSequentialArray(array $array): bool
    {
        return count(array_filter(array_keys($array), 'is_string')) === 0;
    }
}
