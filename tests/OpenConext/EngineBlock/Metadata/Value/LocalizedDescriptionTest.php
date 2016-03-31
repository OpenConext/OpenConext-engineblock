<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlock\Exception\LogicException;
use OpenConext\EngineBlock\Metadata\Value\Common\LocalizedText;
use PHPUnit_Framework_TestCase as UnitTest;
use stdClass;

class LocalizedDescriptionTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function all_elements_must_be_an_instance_of_localized_texts()
    {
        $elements = [
            new LocalizedText('A description', 'en'),
            new LocalizedText('Een omschrijving', 'nl'),
            new stdClass()
        ];

        $this->expectException(InvalidArgumentException::class);

        new LocalizedDescription($elements);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function presence_of_a_description_for_a_locale_can_be_queried()
    {
        $english = new LocalizedText('A description', 'en');
        $dutch = new LocalizedText('Een omschrijving', 'nl');

        $descriptions = new LocalizedDescription([$english, $dutch]);

        $this->assertTrue($descriptions->hasDescriptionForLocale('en'));
        $this->assertTrue($descriptions->hasDescriptionForLocale('nl'));
        $this->assertFalse($descriptions->hasDescriptionForLocale('fr'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $notStringOrEmptyString
     */
    public function presence_of_a_description_can_only_be_queried_with_a_non_empty_string_as_locale($notStringOrEmptyString)
    {
        $descriptions = new LocalizedDescription([]);

        $this->expectException(InvalidArgumentException::class);

        $descriptions->hasDescriptionForLocale($notStringOrEmptyString);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function attempting_to_get_a_description_for_a_locale_that_does_not_exists_causes_an_exception_to_be_thrown()
    {
        $english = new LocalizedText('A description', 'en');
        $dutch   = new LocalizedText('Een omschrijving', 'nl');

        $descriptions = new LocalizedDescription([$english, $dutch]);

        $this->expectException(LogicException::class);

        $descriptions->getDescriptionForLocale('fr');
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function a_description_can_be_retrieved_by_locale()
    {
        $english = new LocalizedText('A description', 'en');
        $dutch   = new LocalizedText('Een omschrijving', 'nl');

        $descriptions = new LocalizedDescription([$english, $dutch]);

        $this->assertSame($english, $descriptions->getDescriptionForLocale('en'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $notStringOrEmptyString
     */
    public function a_description_can_only_be_retrieved_by_locale_if_locale_is_a_non_empty_string($notStringOrEmptyString)
    {
        $descriptions = new LocalizedDescription([]);

        $this->expectException(InvalidArgumentException::class);

        $descriptions->getDescriptionForLocale($notStringOrEmptyString);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_first_element_of_the_requested_locale_is_returned()
    {
        $english          = new LocalizedText('A description', 'en');
        $differentEnglish = new LocalizedText('A different description', 'en');

        $descriptions = new LocalizedDescription([$english, $differentEnglish]);

        $this->assertSame($english, $descriptions->getDescriptionForLocale('en'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function equality_is_verified_on_amount_and_content_of_all_descriptions()
    {
        $english = new LocalizedText('A description', 'en');
        $dutch   = new LocalizedText('Een omschrijving', 'nl');
        $differentEnglish = new LocalizedText('A different description', 'en');

        $base             = new LocalizedDescription([$english, $dutch]);
        $same             = new LocalizedDescription([$english, $dutch]);
        $differentOrder   = new LocalizedDescription([$dutch, $english]);
        $lessElements     = new LocalizedDescription([$english]);
        $moreElements     = new LocalizedDescription([$english, $dutch, $differentEnglish]);
        $differentContent = new LocalizedDescription([$differentEnglish, $dutch]);

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
    public function deserializing_a_serialized_localized_description_yields_an_equal_value_object()
    {
        $original = new LocalizedDescription([
            new LocalizedText('A description', 'en'),
            new LocalizedText('Een omschrijving', 'nl')]
        );

        $deserialized = LocalizedDescription::deserialize($original->serialize());

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

        LocalizedDescription::deserialize($notArray);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function a_localized_description_can_be_cast_to_string()
    {
        $description = new LocalizedDescription([
            new LocalizedText('A description', 'en'),
            new LocalizedText('Een omschrijving', 'nl')
        ]);

        $this->assertInternalType('string', (string) $description);
    }
}
