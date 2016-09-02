<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as UnitTest;

class AttributeManipulationCodeTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $invalidValue
     */
    public function only_non_empty_strings_are_valid_attribute_manipulation_code($invalidValue)
    {
        $this->expectException(InvalidArgumentException::class);

        new AttributeManipulationCode($invalidValue);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function attribute_manipulation_codes_with_the_same_code_are_considered_equal()
    {
        $base      = new AttributeManipulationCode('some $code;');
        $theSame   = new AttributeManipulationCode('some $code;');
        $different = new AttributeManipulationCode('different $code;');

        $this->assertTrue($base->equals($theSame));
        $this->assertFalse($base->equals($different));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_given_code_can_be_retrieved()
    {
        $code = "some\n
        Multiline\n
        \$code;";

        $attributeManipulationCode = new AttributeManipulationCode($code);

        $this->assertSame($code, $attributeManipulationCode->getAttributeManipulationCode());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_a_serialized_attribute_manipulation_code_results_in_an_equal_value_object()
    {
        $original     = new AttributeManipulationCode('$this->isSomeCode();');
        $deserialized = AttributeManipulationCode::deserialize($original->serialize());

        $this->assertTrue($original->equals($deserialized));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $invalidData
     */
    public function deserialization_requires_valid_data($invalidData)
    {
        $this->expectException(InvalidArgumentException::class);

        AttributeManipulationCode::deserialize($invalidData);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function attribute_manipulation_code_can_be_cast_to_string()
    {
        $manipulationCode = new AttributeManipulationCode('some code');

        $this->assertInternalType('string', (string) $manipulationCode);
    }
}
