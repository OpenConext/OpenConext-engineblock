<?php

namespace OpenConext\EngineBlock\Metadata\Value\Common;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as UnitTest;

class LocalizedTextTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $notStringOrEmtpyString
     */
    public function text_must_be_a_non_empty_string($notStringOrEmtpyString)
    {
        $this->expectException(InvalidArgumentException::class);

        new LocalizedText($notStringOrEmtpyString, 'en');
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $notStringOrEmtpyString
     */
    public function language_must_be_a_non_empty_string($notStringOrEmtpyString)
    {
        $this->expectException(InvalidArgumentException::class);

        new LocalizedText('This is some text', $notStringOrEmtpyString);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function text_can_be_retrieved()
    {
        $text = 'This is some text';

        $localizedName = new LocalizedText($text, 'en');

        $this->assertEquals($text, $localizedName->getText());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function language_can_be_retrieved()
    {
        $language = 'en_US';

        $localizedName = new LocalizedText('This is some text', $language);

        $this->assertEquals($language, $localizedName->getLanguage());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function equality_is_verified_on_text_and_language()
    {
        $base              = new LocalizedText('This is some text', 'en');
        $same              = new LocalizedText('This is some text', 'en');
        $differentName     = new LocalizedText('different text', 'en');
        $differentLanguage = new LocalizedText('This is some text', 'en_US');

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($differentName));
        $this->assertFalse($base->equals($differentLanguage));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_a_serialized_localized_text_yields_an_equal_value_object()
    {
        $original = new LocalizedText('This is some text', 'en');

        $deserialized = LocalizedText::deserialize($original->serialize());

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

        LocalizedText::deserialize($notArray);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider invalidDataProvider
     *
     * @param array $invalidData
     */
    public function deserialization_requires_all_required_keys_to_be_present($invalidData)
    {
        $this->expectException(InvalidArgumentException::class);

        LocalizedText::deserialize($invalidData);
    }

    public function invalidDataProvider()
    {
        return [
            'no matching keys' => [['foo' => 'This is some text', 'bar' => 'en_US']],
            'no text'          => [['language' => 'en_US']],
            'no language'      => [['text' => 'This is some text']],
        ];
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function a_localized_text_can_be_cast_to_string()
    {
        $this->assertInternalType('string', (string) new LocalizedText('This is some text', 'en'));
    }
}
