<?php

namespace OpenConext\EngineBlock\Assert;

use PHPUnit_Framework_TestCase as UnitTest;

class AssertionTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Assertion
     */
    public function non_empty_strings_are_valid()
    {
        Assertion::nonEmptyString('0', 'test');
        Assertion::nonEmptyString('text', 'test');
        Assertion::nonEmptyString("new\nlines\nincluded", 'test');
    }

    /**
     * @test
     * @group EngineBlock
     * @group Assertion
     *
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString()
     * @expectedException \OpenConext\EngineBlock\Exception\InvalidArgumentException
     *
     * @param mixed $value
     */
    public function not_strings_or_empty_strings_are_invalid($value)
    {
        Assertion::nonEmptyString($value, 'value');
    }
}
