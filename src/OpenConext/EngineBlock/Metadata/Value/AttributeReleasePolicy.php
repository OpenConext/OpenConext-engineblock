<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Exception\LogicException;
use OpenConext\EngineBlock\Metadata\Value\AttributeReleasePolicy\AttributePolicy;
use OpenConext\Value\Serializable;

/**
 * Due to backwards compatibility it is not verified whether or not a single attribute has multiple policies defined,
 * the first encountered (if any) is the one used.
 * This is a future improvement though, see: https://www.pivotaltracker.com/story/show/115227767
 */
final class AttributeReleasePolicy implements Serializable
{
    /**
     * @var AttributePolicy[]
     */
    private $attributePolicies;

    /**
     * Creates a new AttributeReleasePolicy from a descriptor. The descriptor is an array with attributenames as key
     * and corresponding allowed values as respective values, e.g.:
     * ['attributeA' => ['foo', 'bar*'], 'attributeB' => ['*']]
     *
     * @param array $descriptor
     * @return AttributeReleasePolicy
     */
    public static function fromDescriptor(array $descriptor)
    {
        Assertion::allNonEmptyString(
            array_keys($descriptor),
            'All keys of an AttributeReleasePolicy descriptor must be a non-empty string'
        );
        Assertion::allIsArray(
            array_values($descriptor),
            'All values of an AttributeReleasePolicy descriptor must be an array'
        );

        $attributePolicies = [];
        foreach ($descriptor as $attributeName => $allowedValues) {
            $attributePolicies[] = new AttributePolicy($attributeName, $allowedValues);
        }

        return new self($attributePolicies);
    }

    /**
     ** @param AttributePolicy[] $attributePolicies
     */
    public function __construct(array $attributePolicies)
    {
        Assertion::allIsInstanceOf(
            $attributePolicies,
            'OpenConext\EngineBlock\Metadata\Value\AttributeReleasePolicy\AttributePolicy'
        );

        $this->attributePolicies = $attributePolicies;
    }

    /**
     * @param string $attributeName
     * @return bool
     */
    public function hasPolicyFor($attributeName)
    {
        foreach ($this->attributePolicies as $attributePolicy) {
            if ($attributePolicy->isForAttribute($attributeName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $attributeName
     * @param string $attributeValue
     * @return bool
     */
    public function allowsValueFor($attributeName, $attributeValue)
    {
        if (!$this->hasPolicyFor($attributeName)) {
            throw new LogicException(sprintf(
                'No AttributePolicy has been defined for "%s", did you check with ::hasPolicyFor before verifying?',
                $attributeValue
            ));
        }

        foreach ($this->attributePolicies as $attributePolicy) {
            if (!$attributePolicy->isForAttribute($attributeName)) {
                continue;
            }

            return $attributePolicy->allows($attributeValue);
        }

        return false;
    }

    /**
     * @return AttributePolicy[]
     */
    public function getAttributePolicies()
    {
        return $this->attributePolicies;
    }

    /**
     * @param AttributeReleasePolicy $other
     * @return bool
     */
    public function equals(AttributeReleasePolicy $other)
    {
        if (count($this->attributePolicies) !== count($other->attributePolicies)) {
            return false;
        }

        foreach ($this->attributePolicies as $index => $attributePolicy) {
            if (!$attributePolicy->equals($other->attributePolicies[$index])) {
                return false;
            }
        }

        return true;
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);

        $attributePolicies = array_map(function ($attributePolicy) {
            return AttributePolicy::deserialize($attributePolicy);
        }, $data);

        return new self($attributePolicies);
    }

    public function serialize()
    {
        return array_map(function (AttributePolicy $attributePolicy) {
            return $attributePolicy->serialize();
        }, $this->attributePolicies);
    }

    public function __toString()
    {
        return sprintf('AttributeReleasePolicy[%s]', implode(', ', $this->attributePolicies));
    }
}
