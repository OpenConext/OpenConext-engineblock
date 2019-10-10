<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlock\Assert;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AssertionTest extends TestCase
{
    /**
     * @test
     * @group EngineBlock
     * @group Assertion
     */
    public function non_empty_strings_are_valid()
    {
        $this->expectNotToPerformAssertions();
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
     * @group EngineBlock
     * @group Assertion
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
     * @group EngineBlock
     * @group Assertion
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

    /**
     * @test
     * @group EngineBlock
     * @group Assertion
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     */
    public function valid_hashing_algorithm_only_accepts_non_empty_strings($notStringOrEmptyString)
    {
        $this->expectException(InvalidArgumentException::class);

        Assertion::validHashingAlgorithm($notStringOrEmptyString);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Assertion
     */
    public function an_invalid_hashing_algorithm_causes_the_assertion_to_throw_an_exception()
    {
        $this->expectException(InvalidArgumentException::class);

        Assertion::validHashingAlgorithm('invalid');
    }

    /**
     * @test
     * @group EngineBlock
     * @group Assertion
     * @dataProvider validHashingAlgorithmProvider
     */
    public function existing_hashing_algorithms_are_considered_valid($validHashingAlgorithm)
    {
        $exceptionCaught = false;
        try {
            Assertion::validHashingAlgorithm($validHashingAlgorithm);
        } catch (InvalidArgumentException $exception) {
            $exceptionCaught = true;
        }

        $this->assertFalse($exceptionCaught, 'A valid hashing algorithm should not cause an exception to be thrown');
    }

    public function validHashingAlgorithmProvider()
    {
        return array_map(function ($algorithm) {
            return [$algorithm];
        }, hash_algos());
    }
}
