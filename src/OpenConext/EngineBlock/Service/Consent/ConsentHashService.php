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

use OpenConext\EngineBlock\Authentication\Repository\ConsentRepository;
use OpenConext\EngineBlock\Authentication\Value\ConsentVersion;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\Name;
use SAML2\XML\saml\NameID;
use function array_filter;
use function array_keys;
use function array_values;
use function count;
use function implode;
use function is_array;
use function is_numeric;
use function ksort;
use function serialize;
use function sha1;
use function sort;
use function str_replace;
use function strtolower;
use function unserialize;

final class ConsentHashService implements ConsentHashServiceInterface
{
    /**
     * @var ConsentRepository
     */
    private $consentRepository;

    public function __construct(ConsentRepository $consentHashRepository)
    {
        $this->consentRepository = $consentHashRepository;
    }

    public function retrieveConsentHash(array $parameters): ConsentVersion
    {
        return $this->consentRepository->hasConsentHash($parameters);
    }

    public function storeConsentHash(array $parameters): bool
    {
        return $this->consentRepository->storeConsentHash($parameters);
    }

    public function updateConsentHash(array $parameters): bool
    {
        return $this->consentRepository->updateConsentHash($parameters);
    }

    public function countTotalConsent($consentUid): int
    {
        return $this->consentRepository->countTotalConsent($consentUid);
    }

    /**
     * The old way of calculating the attribute hash, this is not stable as a change of the attribute order,
     * change of case, stray/empty attributes, and renumbered indexes can cause the hash to change. Leaving the
     * user to give consent once again for a service she previously gave consent for.
     */
    public function getUnstableAttributesHash(array $attributes, bool $mustStoreValues): string
    {
        if ($mustStoreValues) {
            ksort($attributes);
            $hashBase = serialize($attributes);
        } else {
            $names = array_keys($attributes);
            sort($names);
            $hashBase = implode('|', $names);
        }
        return sha1($hashBase);
    }

    public function getStableAttributesHash(array $attributes, bool $mustStoreValues) : string
    {
        $nameIdNormalizedAttributes = $this->nameIdNormalize($attributes);
        $lowerCasedAttributes = $this->caseNormalizeStringArray($nameIdNormalizedAttributes);
        $hashBase = $mustStoreValues
            ? $this->createHashBaseWithValues($lowerCasedAttributes)
            : $this->createHashBaseWithoutValues($lowerCasedAttributes);

        return sha1($hashBase);
    }

    private function createHashBaseWithValues(array $lowerCasedAttributes): string
    {
        return serialize($this->sortArray($lowerCasedAttributes));
    }

    private function createHashBaseWithoutValues(array $lowerCasedAttributes): string
    {
        $noEmptyAttributes = $this->removeEmptyAttributes($lowerCasedAttributes);
        $sortedAttributes = $this->sortArray(array_keys($noEmptyAttributes));
        return implode('|', $sortedAttributes);
    }

    /**
     * Lowercases all array keys and values.
     */
    private function caseNormalizeStringArray(array $original): array
    {
        $serialized = serialize($original);
        $lowerCased = strtolower($serialized);
        $unserialized = unserialize($lowerCased);
        return $unserialized;
    }

    /**
     * Recursively sorts an array via the ksort function.  Performs the sort on a copy to avoid side-effects.
     */
    private function sortArray(array $sortMe): array
    {
        $copy = unserialize(serialize($sortMe));
        $sortFunction = 'ksort';
        $copy = $this->removeEmptyAttributes($copy);

        if ($this->isSequentialArray($copy)) {
            $sortFunction = 'sort';
            $copy = $this->renumberIndices($copy);
        }

        $sortFunction($copy);
        foreach ($copy as $key => $value) {
            if (is_array($value)) {
                $sortFunction($value);
                $copy[$key] = $this->sortArray($value);
            }
        }

        return $copy;
    }

    /**
     * Determines whether an array is sequential, by checking to see if there's at no string keys in it.
     */
    private function isSequentialArray(array $array): bool
    {
        return count(array_filter(array_keys($array), 'is_string')) === 0;
    }

    /**
     * Reindexes the values of the array so that any skipped numeric indexes are removed.
     */
    private function renumberIndices(array $array): array
    {
        return array_values($array);
    }

    /**
     * Iterate over an array and unset any empty values.
     */
    private function removeEmptyAttributes(array $array): array
    {
        $copy = unserialize(serialize($array));

        foreach ($copy as $key => $value) {
            if ($this->isBlank($value)) {
                unset($copy[$key]);
            }
        }

        return $copy;
    }

    /**
     * Checks if a value is empty, but allowing 0 as an integer, float and string.  This means the following are allowed:
     * - 0
     * - 0.0
     * - "0"
     * @param $value array|string|integer|float
     */
    private function isBlank($value): bool
    {
        return empty($value) && !is_numeric($value);
    }

    /**
     * NameId objects can not be serialized/unserialized after being lower cased
     * Thats why the object is converted to a simple array representation where only the
     * relevant NameID aspects are stored.
     */
    private function nameIdNormalize(array $attributes): array
    {
        array_walk_recursive($attributes, function (&$value) {
            if ($value instanceof NameID) {
                $value = ['value' => $value->getValue(), 'Format' => $value->getFormat()];
            }
        });
        return $attributes;
    }
}
