<?php

namespace OpenConext\EngineBlock\Assert;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
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
     *
     * @param mixed $value
     */
    public function not_strings_or_empty_strings_are_invalid($value)
    {
        $this->expectException(InvalidArgumentException::class);

        Assertion::nonEmptyString($value, 'value');
    }

    /**
     * @test
     */
    public function a_missing_key_makes_the_assertion_fail()
    {
        $this->expectException(InvalidArgumentException::class);

        $requiredKeys = ['a', 'b'];
        $actualData   = ['a' => 1, 'c' => 2];

        Assertion::keysExist($actualData, $requiredKeys);
    }

    /**
     * @test
     */
    public function keys_exists_assertion_succeeds_if_all_required_keys_are_present_()
    {
        $requiredKeys = ['a', 'b', 'c'];
        $match        = ['c' => 1, 'a' => 2, 'b' => 'foo'];
        $superfluous  = ['d' => 1, 'a' => 2, 'c' => 3, 'b' => 4];

        $exceptionCaught = false;
        try {
            Assertion::keysExist($match, $requiredKeys);
            Assertion::keysExist($superfluous, $requiredKeys);
        } catch (InvalidArgumentException $exception) {
            $exceptionCaught = true;
        }

        $this->assertFalse($exceptionCaught, 'When all required keys are present, no exception should be thrown');
    }
}
