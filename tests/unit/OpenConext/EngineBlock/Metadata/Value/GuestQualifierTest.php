<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as UnitTest;

class GuestQualifierTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function a_guest_qualifier_can_only_be_created_with_a_defined_qualifier()
    {
        $this->expectException(InvalidArgumentException::class);

        new GuestQualifier('This is not a defined qualifier :(');
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider typeAndFactoryMethodProvider
     */
    public function guest_qualifiers_created_with_definitions_are_equal_to_their_factory_method_counterparts(
        $guestQualifier,
        $factoryMethod
    ) {
        $byGuestQualifier = new GuestQualifier($guestQualifier);
        $byFactoryMethod = GuestQualifier::$factoryMethod();

        $this->assertTrue($byFactoryMethod->equals($byGuestQualifier));
    }

    /**
     * @return array
     */
    public function typeAndFactoryMethodProvider()
    {
        return [
            'all'  => [GuestQualifier::QUALIFIER_ALL, 'all'],
            'some' => [GuestQualifier::QUALIFIER_SOME, 'some'],
            'none' => [GuestQualifier::QUALIFIER_NONE, 'none'],
        ];
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_guest_qualifier_can_be_retrieved()
    {
        $guestQualifier = new GuestQualifier(GuestQualifier::QUALIFIER_NONE);

        $this->assertEquals(GuestQualifier::QUALIFIER_NONE, $guestQualifier->getQualifier());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function only_qualifiers_with_the_same_definition_are_equal()
    {
        $base = GuestQualifier::some();
        $some = GuestQualifier::some();
        $none = GuestQualifier::none();
        $all = GuestQualifier::all();

        $this->assertTrue($some->equals($base));
        $this->assertFalse($some->equals($none));
        $this->assertFalse($some->equals($all));
        $this->assertFalse($none->equals($all));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_a_serialized_guest_qualifier_results_in_an_equal_value_object()
    {
        $original = GuestQualifier::some();

        $deserialized = GuestQualifier::deserialize($original->serialize());

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
    public function deserialization_requires_a_valid_data_format($invalidData)
    {
        $this->expectException(InvalidArgumentException::class);

        GuestQualifier::deserialize($invalidData);
    }
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function a_guest_qualifier_can_be_cast_to_string()
    {
        $this->assertInternalType('string', (string) GuestQualifier::some());
    }
}
