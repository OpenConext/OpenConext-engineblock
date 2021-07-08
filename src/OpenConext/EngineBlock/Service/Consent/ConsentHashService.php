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

use PDO;
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
use function strtolower;
use function unserialize;

final class ConsentHashService
{
    /**
     * @var ConsentHashRepository
     */
    private $consentHashRepository;

    public function __construct(ConsentHashRepository $consentHashRepository)
    {
        $this->consentHashRepository = $consentHashRepository;
    }

    public function retrieveConsentHashFromDb(PDO $dbh, array $parameters): bool
    {
        return $this->consentHashRepository->retrieveConsentHashFromDb($dbh, $parameters);
    }

    public function storeConsentHashInDb(PDO $dbh, array $parameters): bool
    {
        return $this->consentHashRepository->storeConsentHashInDb($dbh, $parameters);
    }

    public function countTotalConsent(PDO $dbh, $consentUid): int
    {
        return $this->consentHashRepository->countTotalConsent($dbh, $consentUid);
    }

    public function getUnstableAttributesHash(array $attributes, bool $mustStoreValues): string
    {
        $hashBase = null;
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
        $lowerCasedAttributes = $this->caseNormalizeStringArray($attributes);
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
     * Performs the lowercasing on a copy (which is returned), to avoid side-effects.
     */
    private function caseNormalizeStringArray(array $original): array
    {
        return unserialize(strtolower(serialize($original)));
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
}
