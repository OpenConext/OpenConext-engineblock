<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlock\Exception\LogicException;
use PHPUnit_Framework_TestCase as UnitTest;

class KeywordsTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::nonStringScalarOrEmptyString
     *
     * @param mixed $nonStringScalarOrEmptyString
     */
    public function creation_from_definition_requires_all_keys_to_be_non_empty_string($nonStringScalarOrEmptyString)
    {
        $definition = ['en' => 'OpenConext OpenConext.org', $nonStringScalarOrEmptyString => 'foo bar'];

        $this->expectException(InvalidArgumentException::class);
        Keywords::fromDefinition($definition);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $notStringOrEmptyString
     */
    public function creation_from_definition_requires_the_keywords_to_be_a_non_empty_string($notStringOrEmptyString)
    {
        $definition = ['en' => 'OpenConext OpenConext.org', 'nl' => $notStringOrEmptyString];

        $this->expectException(InvalidArgumentException::class);
        Keywords::fromDefinition($definition);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function creation_from_definition_ensures_that_keywords_are_separated()
    {
        $definition = ['en' => 'OpenConext OpenConext.org'];

        $keywords = Keywords::fromDefinition($definition);

        $localizedKeywords = $keywords->getKeywordsForLocale('en');
        $this->assertEquals(['OpenConext', 'OpenConext.org'], $localizedKeywords->getKeywords());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function creation_from_definition_creates_a_localized_keywords_per_locale()
    {
        $definition = ['en' => 'OpenConext OpenConext.org', 'nl' => 'OpenConext OpenConext.org'];

        $keywords = Keywords::fromDefinition($definition);

        $this->assertTrue($keywords->hasKeywordsForLocale('en'));
        $this->assertTrue($keywords->hasKeywordsForLocale('nl'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function keywords_requires_all_elements_to_be_an_instance_of_localized_keywords()
    {
        $elements = [new LocalizedKeywords('en', ['OpenConext', 'OpenConext.org']), new \stdClass()];

        $this->expectException(InvalidArgumentException::class);
        new Keywords($elements);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function existence_of_localized_keywords_for_a_locale_can_be_queried()
    {
        $keywords = new Keywords([new LocalizedKeywords('en', ['OpenConext', 'OpenConext.org'])]);

        $this->assertTrue($keywords->hasKeywordsForLocale('en'));
        $this->assertFalse($keywords->hasKeywordsForLocale('en_US'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function attempting_to_get_localized_keywords_for_a_locale_that_is_not_defined_triggers_an_exception()
    {
        $keywords = new Keywords([new LocalizedKeywords('en', ['OpenConext', 'OpenConext.org'])]);

        $this->expectException(LogicException::class);
        $keywords->getKeywordsForLocale('nl');
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function keywords_can_be_retrieved_by_locale()
    {
        $localizedKeywords = new LocalizedKeywords('en', ['OpenConext', 'OpenConext.org']);
        $keywords = new Keywords([$localizedKeywords]);

        $this->assertSame($localizedKeywords, $keywords->getKeywordsForLocale('en'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function equality_is_checked_on_all_localized_keywords_in_order()
    {
        $english = new LocalizedKeywords('en', ['OpenConext', 'OpenConext.org']);
        $dutch   = new LocalizedKeywords('nl', ['OpenConextNL', 'OpenConext.org']);
        $deutsch = new LocalizedKeywords('de', ['OpenConextDE', 'OpenConext.org']);

        $base = new Keywords([$english, $dutch]);
        $same = new Keywords([$english, $dutch]);
        $lessKeywords = new Keywords([$english]);
        $moreKeywords = new Keywords([$english, $dutch, $deutsch]);
        $reversedOrder = new Keywords([$dutch, $english]);

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($lessKeywords));
        $this->assertFalse($base->equals($moreKeywords));
        $this->assertFalse($base->equals($reversedOrder));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_keywords_yields_an_equal_value_object()
    {
        $original = new Keywords([new LocalizedKeywords('en', ['OpenConext', 'OpenConext.org'])]);

        $deserialized = Keywords::deserialize($original->serialize());

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

        Keywords::deserialize($notArray);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function keywords_can_be_cast_to_string()
    {
        $this->assertInternalType(
            'string',
            (string) new Keywords([new LocalizedKeywords('en', ['OpenConext', 'OpenConext.org'])])
        );
    }
}
