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

use InvalidArgumentException;
use function array_key_exists;

class AttributeReleasePolicy
{
    const WILDCARD_CHARACTER = '*';

    /**
     * Holds attribute rule values with optional 'source'.
     *
     * Non-aggregated attributes are stored in the format:
     *
     *   attribute name => [value, value, value]
     *
     * Attributes aggregated from different sources specify a source along with the value:
     *
     *   attribute name => [
     *      [ 'soure' => '...', 'value' => '...' ]
     *   ]
     *
     * @var array
     */
    private $attributeRules;

    /**
     * @param array $attributeRules
     */
    public function __construct(array $attributeRules)
    {
        foreach ($attributeRules as $key => $rules) {
            if (!is_string($key)) {
                throw new InvalidArgumentException(sprintf('Invalid key: "%s"', var_export($key, true)));
            }

            if (!is_array($rules)) {
                throw new InvalidArgumentException(
                    sprintf('Invalid values for attribute "%s", not an array: "%s"', $key, var_export($rules, true))
                );
            }

            foreach ($rules as $rule) {
                $this->validateRule($key, $rule);
            }
        }

        $this->attributeRules = $attributeRules;
    }

    /**
     * @param string $key
     * @param mixed $rule
     * @throws InvalidArgumentException
     */
    private function validateRule($key, $rule)
    {
        if (is_array($rule)) {
            if (!isset($rule['value'])) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Invalid value for attribute "%s", rule must contain a value key, got: "%s"',
                        $key,
                        var_export($rule, true)
                    )
                );
            }

            if (isset($rule['release_as']) && is_numeric($rule['release_as'])) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Invalid release as for attribute "%s", attribute cannot be numeric, got: "%s"',
                        $key,
                        (string)$rule['release_as']
                    )
                );
            }

            $value = $rule['value'];
        } else {
            $value = $rule;
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException(
                sprintf('Invalid value for attribute "%s", not a string: "%s"', $key, var_export($value, true))
            );
        }
    }

    /**
     * Return all attribute rules eligible for attribute aggregation.
     *
     * A rule is eligible for attribute aggregation if it contains a source.
     *
     * @return array
     */
    public function getRulesWithSourceSpecification(): array
    {
        $rulesWithSource = [];

        foreach ($this->attributeRules as $name => $rules) {
            $rulesWithSource[$name] = array_filter(
                $rules,
                function ($rule) {
                    return isset($rule['source']) && $rule['source'] !== 'idp';
                }
            );
        }

        return array_filter($rulesWithSource);
    }

    public function getRulesWithReleaseAsSpecification(): array
    {
        $rulesWithReleaseAs = [];

        foreach ($this->attributeRules as $name => $rules) {
            $rulesWithReleaseAs[$name] = array_filter(
                $rules,
                function ($rule) {
                    return isset($rule['release_as']);
                }
            );
        }

        return array_filter($rulesWithReleaseAs);
    }

    public function findNameIdSubstitute(): ?string
    {
        foreach ($this->attributeRules as $name => $rules) {
            foreach ($rules as $rule) {
                if (isset($rule['use_as_nameid']) && $rule['use_as_nameid'] === true) {
                    if (array_key_exists('release_as', $rule)) {
                        return $rule['release_as'];
                    }
                    return $name;
                }
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function getAttributeNames()
    {
        return array_keys($this->attributeRules);
    }

    /**
     * @param $attributeName
     * @return bool
     */
    public function hasAttribute($attributeName)
    {
        return isset($this->attributeRules[$attributeName]);
    }

    /**
     * @param $attributeName
     * @param $attributeValue
     * @return bool
     */
    public function isAllowed($attributeName, $attributeValue)
    {
        if (!$this->hasAttribute($attributeName)) {
            return false;
        }

        foreach ($this->attributeRules[$attributeName] as $rule) {
            $allowedValue = $this->getRuleValue($rule);

            if ($attributeValue === $allowedValue) {
                // Literal match.
                return true;
            }

            if ($allowedValue === self::WILDCARD_CHARACTER) {
                // Only a single wildcard character, all values are permitted.
                return true;
            }

            // We support wildcard matching at the end only, like 'some*' would match 'someValue' or 'somethingElse'
            if (substr($allowedValue, -1) !== self::WILDCARD_CHARACTER) {
                // Not a supported pattern
                continue;
            }

            // Would contain 'some'
            $patternStart = substr($allowedValue, 0, -1);

            // Does $attributeValue start with 'some'?
            if (strpos($attributeValue, $patternStart) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Read the value of an ARP rule, ignoring the source.
     *
     * @param $rule
     * @return string
     */
    private function getRuleValue($rule)
    {
        if (isset($rule['value'])) {
            return (string) $rule['value'];
        }

        return (string) $rule;
    }

    /**
     * Loads the motivation text for an attribute.
     *
     * @param $attributeName
     * @return string
     */
    public function getMotivation($attributeName)
    {
        if (!$this->hasAttribute($attributeName)) {
            return;
        }

        if (empty($this->attributeRules[$attributeName][0]['motivation'])) {
            return;
        }

        return $this->attributeRules[$attributeName][0]['motivation'];
    }

    /**
     * Loads the first source it finds in the list of attribute rules for the given attributeName.
     *
     * @param $attributeName
     * @return string
     */
    public function getSource($attributeName)
    {
        if ($this->hasAttribute($attributeName) && isset($this->attributeRules[$attributeName][0]['source'])) {
            return $this->attributeRules[$attributeName][0]['source'];
        }
        return 'idp';
    }

    /**
     * @return array
     */
    public function getAttributeRules()
    {
        return $this->attributeRules;
    }
}
