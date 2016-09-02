<?php

namespace OpenConext\EngineBlock\Metadata\Value\AttributeReleasePolicy;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as UnitTest;

class AttributePolicyTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $invalidArgument
     */
    public function attribute_name_must_be_a_non_empty_string($invalidArgument)
    {
        $this->expectException(InvalidArgumentException::class);

        new AttributePolicy($invalidArgument, ['foo']);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $invalidArgument
     */
    public function attribute_values_must_be_an_array_of_non_empty_strings($invalidArgument)
    {
        $this->expectException(InvalidArgumentException::class);

        new AttributePolicy('attributeName', [$invalidArgument]);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function a_policy_belongs_to_its_named_attribute()
    {
        $policy = new AttributePolicy('attribute', ['allowed']);

        $this->assertTrue($policy->isForAttribute('attribute'));
        $this->assertFalse($policy->isForAttribute('some other attribute'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function a_value_that_strictly_matches_a_non_wildcard_allowed_value_is_allowed()
    {
        $policy = new AttributePolicy('attribute', ['allowed']);

        $this->assertTrue($policy->allows('allowed'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function a_value_that_does_not_strictly_match_a_non_wildcard_allowed_value_is_not_allowed()
    {
        $policy = new AttributePolicy('attribute', ['allowed']);

        $this->assertFalse($policy->allows('ALloweD'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function a_wildcard_allows_all_values()
    {
        $policy = new AttributePolicy('attribute', [AttributePolicy::WILDCARD]);

        $this->assertTrue($policy->allows('yes'));
        $this->assertTrue($policy->allows('absolutely'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function a_partial_wildcard_allows_lefthand_matches()
    {
        $policy = new AttributePolicy('attribute', ['left' . AttributePolicy::WILDCARD]);

        $this->assertTrue($policy->allows('left'));
        $this->assertTrue($policy->allows('left and something else'));
        $this->assertFalse($policy->allows('not left'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function righthand_matches_are_with_a_wildcard_are_not_processed()
    {
        $policy = new AttributePolicy('attribute', [AttributePolicy::WILDCARD . 'right']);

        $this->assertTrue($policy->allows('*right'));
        $this->assertFalse($policy->allows('something right'));
        $this->assertFalse($policy->allows('not right'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_attribute_name_can_be_retrieved()
    {
        $attribute = 'some:urn:attribute:name';
        $policy = new AttributePolicy($attribute, ['allowed', 'values']);

        $this->assertEquals($attribute, $policy->getAttributeName());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_allowed_values_can_be_retrieved()
    {
        $allowedValues = ['allowed', 'values'];
        $policy        = new AttributePolicy('some:urn:attribute:name', $allowedValues);

        $this->assertEquals($allowedValues, $policy->getAllowedValues());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function equality_is_verified_on_attribute_name_and_allowed_values()
    {
        $base                   = new AttributePolicy('some:urn:attribute:name', ['allowed', 'values']);
        $same                   = new AttributePolicy('some:urn:attribute:name', ['allowed', 'values']);
        $otherAttribute         = new AttributePolicy('some:other:attribute', ['allowed', 'values']);
        $lessAllowedValues      = new AttributePolicy('some:urn:attribute:name', ['allowed']);
        $differentAllowedValues = new AttributePolicy('some:urn:attribute:name', ['different', 'values']);
        $moreAllowedValues      = new AttributePolicy('some:other:attribute', ['allowed', 'values', 'and then']);

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($otherAttribute));
        $this->assertFalse($base->equals($lessAllowedValues));
        $this->assertFalse($base->equals($differentAllowedValues));
        $this->assertFalse($base->equals($moreAllowedValues));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_a_serialized_attribute_policy_results_in_an_equal_value_object()
    {
        $original = new AttributePolicy('some:urn:attribute:name', ['allowed', 'values']);
        $deserialized = AttributePolicy::deserialize($original->serialize());

        $this->assertTrue($original->equals($deserialized));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notArray
     *
     * @param mixed $notArray
     */
    public function deserialization_requires_data_to_be_an_array($notArray)
    {
        $this->expectException(InvalidArgumentException::class);

        AttributePolicy::deserialize($notArray);
    }

    /**
     * @test
     * @group        engineblock
     * @group        metadata
     *
     * @dataProvider invalidDeserializationDataProvider
     *
     * @param array $invalidData
     */
    public function deserialization_requires_attribute_name_and_allowed_values_as_keys($invalidData)
    {
        $this->expectException(InvalidArgumentException::class);

        AttributePolicy::deserialize($invalidData);
    }

    public function invalidDeserializationDataProvider()
    {
        return [
            'missing attribute_name' => [['allowed_values' => ['allowed']]],
            'missing allowed_values' => [['attribute_name' => 'some:urn:attribute:name']],
            'missing both'           => [['some' => 'thing']]
        ];
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function an_attribute_policy_can_be_cast_to_string()
    {
        $policy = new AttributePolicy('some:urn:attribute:name', ['allowed', 'values']);

        $this->assertInternalType('string', (string) $policy);
    }
}
