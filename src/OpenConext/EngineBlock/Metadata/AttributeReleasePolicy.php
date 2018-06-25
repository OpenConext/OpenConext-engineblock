<?php

namespace OpenConext\EngineBlock\Metadata;

/**
 * Class AttributeReleasePolicy
 * @package OpenConext\EngineBlock\Metadata
 */
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
                throw new \InvalidArgumentException('Invalid key: ' . var_export($key, true));
            }

            if (!is_array($rules)) {
                throw new \InvalidArgumentException(
                    "Invalid values for attribute '$key', not an array: " . var_export($rules, true)
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
     * @throws \InvalidArgumentException
     */
    private function validateRule($key, $rule)
    {
        if (is_array($rule)) {
            if (!isset($rule['value'])) {
                throw new \InvalidArgumentException(
                    "Invalid value for attribute '$key', rule must contain a 'value' key, got: " . var_export($rule, true)
                );
            }

            $value = $rule['value'];
        } else {
            $value = $rule;
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException(
                "Invalid value for attribute '$key', not a string: " . var_export($value, true)
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
    public function getRulesWithSourceSpecification()
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
