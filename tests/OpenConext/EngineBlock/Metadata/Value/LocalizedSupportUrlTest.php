<?php

namespace OpenConext\EngineBlock\Metadata\Value\Common;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlock\Exception\LogicException;
use OpenConext\EngineBlock\Metadata\Value\LocalizedSupportUrl;
use OpenConext\Value\Saml\Metadata\Common\LocalizedUri;
use PHPUnit_Framework_TestCase as UnitTest;
use stdClass;

class LocalizedSupportUrlTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function all_elements_must_be_an_instance_of_localized_uris()
    {
        $elements = [
            new LocalizedUri('https://en.domain.invalid/', 'en'),
            new LocalizedUri('https://nl.domain.invalid/', 'nl'),
            new stdClass()
        ];

        $this->expectException(InvalidArgumentException::class);

        new LocalizedSupportUrl($elements);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function presence_of_a_support_url_for_a_locale_can_be_queried()
    {
        $english = new LocalizedUri('https://en.domain.invalid/', 'en');
        $dutch   = new LocalizedUri('https://nl.domain.invalid/', 'nl');

        $supportUrls = new LocalizedSupportUrl([$english, $dutch]);

        $this->assertTrue($supportUrls->hasSupportUrlForLocale('en'));
        $this->assertTrue($supportUrls->hasSupportUrlForLocale('nl'));
        $this->assertFalse($supportUrls->hasSupportUrlForLocale('fr'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $notStringOrEmptyString
     */
    public function presence_of_a_support_url_can_only_be_queried_with_a_non_empty_string_as_locale(
        $notStringOrEmptyString
    ) {
        $supportUrls = new LocalizedSupportUrl([]);

        $this->expectException(InvalidArgumentException::class);

        $supportUrls->hasSupportUrlForLocale($notStringOrEmptyString);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function attempting_to_get_a_support_url_for_a_locale_that_does_not_exists_causes_an_exception_to_be_thrown()
    {
        $english = new LocalizedUri('https://en.domain.invalid/', 'en');
        $dutch   = new LocalizedUri('https://nl.domain.invalid/', 'nl');

        $supportUrls = new LocalizedSupportUrl([$english, $dutch]);

        $this->expectException(LogicException::class);

        $supportUrls->getSupportUrlForLocale('fr');
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function a_support_url_can_be_retrieved_by_locale()
    {
        $english = new LocalizedUri('https://en.domain.invalid/', 'en');
        $dutch   = new LocalizedUri('https://nl.domain.invalid/', 'nl');

        $supportUrls = new LocalizedSupportUrl([$english, $dutch]);

        $this->assertSame($english, $supportUrls->getSupportUrlForLocale('en'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $notStringOrEmptyString
     */
    public function a_support_url_can_only_be_retrieved_by_locale_if_locale_is_a_non_empty_string(
        $notStringOrEmptyString
    ) {
        $supportUrls = new LocalizedSupportUrl([]);

        $this->expectException(InvalidArgumentException::class);

        $supportUrls->getSupportUrlForLocale($notStringOrEmptyString);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_first_element_of_the_requested_locale_is_returned()
    {
        $english          = new LocalizedUri('https://en.domain.invalid/', 'en');
        $differentEnglish = new LocalizedUri('https://domain.invalid/en', 'en');

        $supportUrls = new LocalizedSupportUrl([$english, $differentEnglish]);

        $this->assertSame($english, $supportUrls->getSupportUrlForLocale('en'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function equality_is_verified_on_amount_and_content_of_all_support_url()
    {
        $english          = new LocalizedUri('https://en.domain.invalid/', 'en');
        $dutch            = new LocalizedUri('https://nl.domain.invalid/', 'nl');
        $differentEnglish = new LocalizedUri('https://domain.invalid/en', 'en');

        $base             = new LocalizedSupportUrl([$english, $dutch]);
        $same             = new LocalizedSupportUrl([$english, $dutch]);
        $differentOrder   = new LocalizedSupportUrl([$dutch, $english]);
        $lessElements     = new LocalizedSupportUrl([$english]);
        $moreElements     = new LocalizedSupportUrl([$english, $dutch, $differentEnglish]);
        $differentContent = new LocalizedSupportUrl([$differentEnglish, $dutch]);

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
    public function deserializing_serialized_localized_support_url_yields_an_equal_value_object()
    {
        $original = new LocalizedSupportUrl(
            [
                new LocalizedUri('https://en.domain.invalid/', 'en'),
                new LocalizedUri('https://nl.domain.invalid/', 'nl')
            ]
        );

        $deserialized = LocalizedSupportUrl::deserialize($original->serialize());

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

        LocalizedSupportUrl::deserialize($notArray);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function a_localized_support_url_can_be_cast_to_string()
    {
        $localizedSupportUrls = new LocalizedSupportUrl(
            [
                new LocalizedUri('https://en.domain.invalid/', 'en'),
                new LocalizedUri('https://nl.domain.invalid/', 'nl')
            ]
        );

        $this->assertInternalType('string', (string) $localizedSupportUrls);
    }
}
