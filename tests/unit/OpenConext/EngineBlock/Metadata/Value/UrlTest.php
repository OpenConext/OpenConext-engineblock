<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\Value\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as UnitTest;

class UrlTest extends UnitTest
{
    /**
     * @test
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     * @expectedException InvalidArgumentException
     *
     * @param mixed $notStringOrEmptyString
     */
    public function url_must_be_a_non_empty_string($notStringOrEmptyString)
    {
        new Url($notStringOrEmptyString);
    }

    /**
     * @test
     * @group Metadata
     */
    public function uri_can_be_retrieved()
    {
        $uri = 'https://en.domain.invalid/';

        $url = new Url($uri);

        $this->assertEquals($uri, $url->getUrl());
    }

    /**
     * @test
     * @group Metadata
     */
    public function equality_is_verified_on_uri()
    {
        $base              = new Url('https://en.domain.invalid/');
        $same              = new Url('https://en.domain.invalid/');
        $differentUri      = new Url('https://nl.domain.invalid/');

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($differentUri));
    }

    /**
     * @test
     * @group Metadata
     */
    public function deserializing_a_serialized_url_yields_an_equal_value_object()
    {
        $original = new Url('https://en.domain.invalid/');

        $deserialized = Url::deserialize($original->serialize());

        $this->assertTrue($original->equals($deserialized));
    }

    /**
     * @test
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notArray
     * @expectedException InvalidArgumentException
     *
     * @param mixed $notArray
     */
    public function deserialization_requires_data_to_be_an_array($notArray)
    {
        Url::deserialize($notArray);
    }

    /**
     * @test
     * @group Metadata
     *
     * @expectedException InvalidArgumentException
     *
     */
    public function deserialization_requires_all_required_keys_to_be_present()
    {
        Url::deserialize(array('foo' => 'https://en.domain.invalid/'));
    }

    /**
     * @test
     * @group Metadata
     */
    public function a_localized_uri_can_be_cast_to_string()
    {
        $this->assertInternalType('string', (string) new Url('some:uri'));
    }
}
