<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as UnitTest;

class LocalizedKeywordsTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $notStringOrEmptyString
     */
    public function locale_must_be_a_non_empty_string($notStringOrEmptyString)
    {
        $this->expectException(InvalidArgumentException::class);

        new LocalizedKeywords($notStringOrEmptyString, ['OpenConext']);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notString
     *
     * @param mixed $notString
     */
    public function keywords_must_all_be_a_string($notString)
    {
        $keywords = ['OpenConext', $notString, 'OpenConext.org'];

        $this->expectException(InvalidArgumentException::class);

        new LocalizedKeywords('en', $keywords);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_locale_can_be_retrieved()
    {
        $locale = 'en_US';

        $localizedKeywords = new LocalizedKeywords($locale, ['OpenConext']);

        $this->assertEquals($locale, $localizedKeywords->getLocale());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_keywords_can_be_retrieved()
    {
        $keywords = ['OpenConext', 'OpenConext.org', 'Saml'];

        $localizedKeywords = new LocalizedKeywords('en', $keywords);

        $this->assertEquals($keywords, $localizedKeywords->getKeywords());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function equality_is_verified_on_locale_and_keywords()
    {
        $base              = new LocalizedKeywords('en', ['OpenConext', 'OpenConext.org']);
        $same              = new LocalizedKeywords('en', ['OpenConext', 'OpenConext.org']);
        $differentLocale   = new LocalizedKeywords('nl', ['OpenConext', 'OpenConext.org']);
        $differentKeywords = new LocalizedKeywords('en', ['quuz', 'quux']);
        $reversedKeywords  = new LocalizedKeywords('en', ['OpenConext.org', 'OpenConext']);
        $lessKeywords      = new LocalizedKeywords('en', ['OpenConext']);
        $moreKeywords      = new LocalizedKeywords('en', ['OpenConext', 'OpenConext.org', 'Saml']);

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($differentLocale));
        $this->assertFalse($base->equals($differentKeywords));
        $this->assertFalse($base->equals($reversedKeywords));
        $this->assertFalse($base->equals($lessKeywords));
        $this->assertFalse($base->equals($moreKeywords));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_serialized_keywords_yield_an_equal_value_object()
    {
        $original = new LocalizedKeywords('en', ['OpenConext', 'OpenConext.org']);

        $deserialized = LocalizedKeywords::deserialize($original->serialize());

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

        LocalizedKeywords::deserialize($notArray);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider invalidDataProvider
     *
     * @param mixed $invalidData
     */
    public function deserialization_requires_locale_and_keywords_keys_to_be_present($invalidData)
    {
        $this->expectException(InvalidArgumentException::class);

        LocalizedKeywords::deserialize($invalidData);
    }

    public function invalidDataProvider()
    {
        return [
            'no matching keys' => [['foo' => 'en', 'bar' => ['OpenConext', 'OpenConext.org']]],
            'no locale'        => [['keywords' => ['OpenConext', 'OpenConext.org']]],
            'no keywords'      => [['locale' => 'en']],
        ];
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function localized_keywords_can_be_cast_to_string()
    {
        $this->assertInternalType('string', (string) new LocalizedKeywords('en', ['OpenConext', 'OpenConext.org']));
    }
}
