<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as UnitTest;

class LogoTest extends UnitTest
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
    public function url_must_be_a_non_empty_string($notStringOrEmptyString)
    {
        $this->expectException(InvalidArgumentException::class);

        new Logo($notStringOrEmptyString, 10, 10);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notInteger
     *
     * @param mixed $notInteger
     */
    public function width_must_be_an_integer($notInteger)
    {
        $this->expectException(InvalidArgumentException::class);

        new Logo('https://cdn.domain.invalid/img.png', $notInteger, 10);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notInteger
     *
     * @param mixed $notInteger
     */
    public function height_must_be_an_integer($notInteger)
    {
        $this->expectException(InvalidArgumentException::class);

        new Logo('https://cdn.domain.invalid/img.png', 10, $notInteger);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_url_can_be_retrieved()
    {
        $url = 'https://cdn.domain.invalid/img.png';
        $logo = new Logo($url, 10, 10);

        $this->assertEquals($url, $logo->getUrl());
    }
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_width_can_be_retrieved()
    {
        $width = 150;
        $logo = new Logo('https://cdn.domain.invalid/img.png', $width, 10);

        $this->assertEquals($width, $logo->getWidth());
    }
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_height_can_be_retrieved()
    {
        $height = 125;
        $logo = new Logo('https://cdn.domain.invalid/img.png', 10, $height);

        $this->assertEquals($height, $logo->getHeight());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function equality_is_verified_on_all_attributes()
    {
        $base            = new Logo('https://cdn.domain.invalid/img.png', 150, 150);
        $same            = new Logo('https://cdn.domain.invalid/img.png', 150, 150);
        $differentUrl    = new Logo('https://uhoh.nope.invalid/img.gif', 150, 150);
        $differentWidth  = new Logo('https://cdn.domain.invalid/img.png', 10, 150);
        $differentHeight = new Logo('https://cdn.domain.invalid/img.png', 150, 10);

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($differentUrl));
        $this->assertFalse($base->equals($differentWidth));
        $this->assertFalse($base->equals($differentHeight));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_a_serialized_logo_yields_an_equal_value_object()
    {
        $original = new Logo('https://cdn.domain.invalid/img.png', 150, 150);

        $deserialized = Logo::deserialize($original->serialize());

        $this->assertTrue($original->equals($deserialized));
    }

    /**
     * @test
     * @group        EngineBlock
     * @group        Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notArray
     *
     * @param mixed $notArray
     */
    public function deserialization_requires_data_to_be_an_array($notArray)
    {
        $this->expectException(InvalidArgumentException::class);

        Logo::deserialize($notArray);
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
    public function deserialization_requires_all_keys_to_be_present($invalidData)
    {
        $this->expectException(InvalidArgumentException::class);

        Logo::deserialize($invalidData);
    }

    /**
     * @return array
     */
    public function invalidDataProvider()
    {
        return [
            'no matching keys' => [['foo' => 'https://cdn.domain.invalid/img.png', 'bar' => 10, 'baz' => 10]],
            'no url'           => [['width' => 10, 'height' => 10]],
            'no width'         => [['foo' => 'https://cdn.domain.invalid/img.png', 'height' => 10]],
            'no height'        => [['foo' => 'https://cdn.domain.invalid/img.png', 'width' => 10]],
        ];
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function a_logo_can_be_cast_to_string()
    {
        $this->assertInternalType('string', (string) (new Logo('https://cdn.domain.invalid/img.png', 10, 10)));
    }
}
