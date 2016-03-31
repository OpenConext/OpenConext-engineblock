<?php

namespace OpenConext\EngineBlock\Metadata\Value\AttributeReleasePolicy;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Serializable;

final class AttributePolicy implements Serializable
{
    const WILDCARD = '*';

    /**
     * @var string
     */
    private $attributeName;

    /**
     * @var string[]
     */
    private $allowedValues;

    /**
     **
     * @param string   $attributeName
     * @param string[] $values
     */
    public function __construct($attributeName, array $values)
    {
        Assertion::nonEmptyString($attributeName, 'attributeName');
        Assertion::allNonEmptyString($values, 'value');

        $this->attributeName = $attributeName;
        $this->allowedValues = $values;
    }

    /**
     * @param string $attributeName
     * @return bool
     */
    public function isForAttribute($attributeName)
    {
        return $this->attributeName === $attributeName;
    }

    /**
     * @param string $value
     * @return bool
     */
    public function allows($value)
    {
        Assertion::nonEmptyString($value, 'value');

        foreach ($this->allowedValues as $allowed) {
            if ($allowed === self::WILDCARD) {
                return true;
            }

            if ($allowed === $value) {
                return true;
            }

            if (substr($allowed, -1) !== self::WILDCARD) {
                continue;
            }

            $partial = substr($allowed, 0, -1);
            if (strpos($value, $partial) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param AttributePolicy $other
     * @return bool
     */
    public function equals(AttributePolicy $other)
    {
        return $this->attributeName === $other->attributeName
                && $this->allowedValues === $other->allowedValues;
    }

    /**
     * @return string
     */
    public function getAttributeName()
    {
        return $this->attributeName;
    }

    /**
     * @return string[]
     */
    public function getAllowedValues()
    {
        return $this->allowedValues;
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);
        Assertion::keysExist($data, ['attribute_name', 'allowed_values']);

        return new self($data['attribute_name'], $data['allowed_values']);
    }

    public function serialize()
    {
        return [
            'attribute_name' => $this->attributeName,
            'allowed_values' => $this->allowedValues
        ];
    }

    public function __toString()
    {
        return sprintf(
            'AttributePolicy(attributeName="%s", allowedValues=["%s"])',
            $this->attributeName,
            implode('", "', $this->allowedValues)
        );
    }
}
