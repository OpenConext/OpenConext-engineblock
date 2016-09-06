<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlock\Exception\LogicException;
use OpenConext\Value\Saml\Metadata\Common\LocalizedName;
use PHPUnit_Framework_TestCase as UnitTest;
use stdClass;

class LocalizedServiceNameTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function all_elements_must_be_an_instance_of_localized_texts()
    {
        $elements = [
            new LocalizedName('OpenConext', 'en'),
            new LocalizedName('OpenConext', 'nl'),
            new stdClass()
        ];

        $this->expectException(InvalidArgumentException::class);

        new LocalizedServiceName($elements);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function presence_of_a_service_name_for_a_locale_can_be_queried()
    {
        $english = new LocalizedName('OpenConext', 'en');
        $dutch   = new LocalizedName('OpenConext', 'nl');

        $serviceNames = new LocalizedServiceName([$english, $dutch]);

        $this->assertTrue($serviceNames->hasServiceNameForLocale('en'));
        $this->assertTrue($serviceNames->hasServiceNameForLocale('nl'));
        $this->assertFalse($serviceNames->hasServiceNameForLocale('fr'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $notStringOrEmptyString
     */
    public function presence_of_a_service_name_can_only_be_queried_with_a_non_empty_string_as_locale(
        $notStringOrEmptyString
    ) {
        $serviceNames = new LocalizedServiceName([]);

        $this->expectException(InvalidArgumentException::class);

        $serviceNames->hasServiceNameForLocale($notStringOrEmptyString);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function attempting_to_get_a_service_name_for_a_locale_that_does_not_exists_causes_an_exception_to_be_thrown()
    {
        $english = new LocalizedName('OpenConext', 'en');
        $dutch   = new LocalizedName('OpenConext', 'nl');

        $serviceNames = new LocalizedServiceName([$english, $dutch]);

        $this->expectException(LogicException::class);

        $serviceNames->getServiceNameForLocale('fr');
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function a_service_name_can_be_retrieved_by_locale()
    {
        $english = new LocalizedName('OpenConext', 'en');
        $dutch   = new LocalizedName('OpenConext', 'nl');

        $serviceNames = new LocalizedServiceName([$english, $dutch]);

        $this->assertSame($english, $serviceNames->getServiceNameForLocale('en'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $notStringOrEmptyString
     */
    public function a_service_name_can_only_be_retrieved_by_locale_if_locale_is_a_non_empty_string(
        $notStringOrEmptyString
    ) {
        $serviceNames = new LocalizedServiceName([]);

        $this->expectException(InvalidArgumentException::class);

        $serviceNames->getServiceNameForLocale($notStringOrEmptyString);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_first_element_of_the_requested_locale_is_returned()
    {
        $english          = new LocalizedName('OpenConext', 'en');
        $differentEnglish = new LocalizedName('A different name', 'en');

        $serviceNames = new LocalizedServiceName([$english, $differentEnglish]);

        $this->assertSame($english, $serviceNames->getServiceNameForLocale('en'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function equality_is_verified_on_amount_and_content_of_all_service_names()
    {
        $english          = new LocalizedName('OpenConext', 'en');
        $dutch            = new LocalizedName('OpenConext', 'nl');
        $differentEnglish = new LocalizedName('A different name', 'en');

        $base             = new LocalizedServiceName([$english, $dutch]);
        $same             = new LocalizedServiceName([$english, $dutch]);
        $differentOrder   = new LocalizedServiceName([$dutch, $english]);
        $lessElements     = new LocalizedServiceName([$english]);
        $moreElements     = new LocalizedServiceName([$english, $dutch, $differentEnglish]);
        $differentContent = new LocalizedServiceName([$differentEnglish, $dutch]);

        $this->assertTrue($base->equals($same));
        $this->assertTrue($base->equals($differentOrder));
        $this->assertFalse($base->equals($lessElements));
        $this->assertFalse($base->equals($moreElements));
        $this->assertFalse($base->equals($differentContent));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_a_serialized_localized_service_name_yields_an_equal_value_object()
    {
        $original = new LocalizedServiceName(
            [
                new LocalizedName('OpenConext', 'en'),
                new LocalizedName('OpenConext', 'nl')
            ]
        );

        $deserialized = LocalizedServiceName::deserialize($original->serialize());

        $this->assertTrue($original->equals($deserialized));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     * @dataProvider \OpenConext\TestDataProvider::notArray
     *
     * @param mixed $notArray
     */
    public function deserialization_requires_data_to_be_an_array($notArray)
    {
        $this->expectException(InvalidArgumentException::class);

        LocalizedServiceName::deserialize($notArray);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function a_localized_service_name_can_be_cast_to_string()
    {
        $description = new LocalizedServiceName(
            [
                new LocalizedName('OpenConext', 'en'),
                new LocalizedName('OpenConext', 'nl')
            ]
        );

        $this->assertInternalType('string', (string) $description);
    }
}
