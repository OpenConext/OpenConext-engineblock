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

namespace OpenConext\EngineBlock\Metadata;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LoaTest extends TestCase
{
    /**
     * @dataProvider provideValidLoaParameters
     */
    public function test_loa_happy_flow($level, $identifier, $expectationDescription)
    {
        $loa = Loa::create($level, $identifier);

        $this->assertEquals($level, $loa->getLevel(), $expectationDescription);
        $this->assertEquals($identifier, $loa->getIdentifier(), $expectationDescription);
    }

    /**
     * @dataProvider provideInvalidLoaParameters
     */
    public function test_loa_sad_flow($level, $identifier, $expectedException)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedException);
        Loa::create($level, $identifier);
    }

    public function test_loa_can_be_compared_to_other_loa()
    {
        $loa1 = Loa::create(1, 'https://vm.openconext.org/assurance/loa1');
        $loa2 = Loa::create(2, 'https://vm.openconext.org/assurance/loa2');
        $loa3 = Loa::create(3, 'https://vm.openconext.org/assurance/loa3');

        $this->assertTrue($loa3->levelIsHigherOrEqualTo($loa3->getLevel()));
        $this->assertTrue($loa3->levelIsHigherOrEqualTo($loa2->getLevel()));
        $this->assertTrue($loa3->levelIsHigherOrEqualTo($loa1->getLevel()));

        $this->assertTrue($loa2->levelIsHigherOrEqualTo($loa2->getLevel()));
        $this->assertTrue($loa2->levelIsHigherOrEqualTo($loa1->getLevel()));
        $this->assertFalse($loa2->levelIsHigherOrEqualTo($loa3->getLevel()));

        $this->assertTrue($loa1->levelIsHigherOrEqualTo($loa1->getLevel()));
        $this->assertFalse($loa1->levelIsHigherOrEqualTo($loa2->getLevel()));
        $this->assertFalse($loa1->levelIsHigherOrEqualTo($loa3->getLevel()));
    }

    /**
     * @dataProvider provideInvalidComparisonOptions
     */
    public function test_loa_comparison_is_to_be_used_correctly($badComparisonOption, $expectedException)
    {
        $loa = Loa::create(1, 'https://vm.openconext.org/assurance/loa2');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedException);

        $loa->levelIsHigherOrEqualTo($badComparisonOption);
    }

    public function provideInvalidComparisonOptions()
    {
        return [
            ['1', 'Provide the integer value representing the LoA level'],
            [true, 'Provide the integer value representing the LoA level'],
            [false, 'Provide the integer value representing the LoA level'],
            [1.5, 'Provide the integer value representing the LoA level'],
            [9999999999999999999999999999, 'Provide the integer value representing the LoA level'],
            [-9999999999999999999999999999, 'Provide the integer value representing the LoA level'],
            [0, 'Please provide a positive integer value'],
            [-1, 'Please provide a positive integer value'],
        ];
    }

    public function provideValidLoaParameters()
    {
        return [
            [1, 'https://vm.openconext.nl/loa1', 'A level 1 LoA'],
            [2, 'https://vm.openconext.nl/loa2', 'A level 2 LoA'],
            [3, 'https://vm.openconext.nl/loa3', 'A level 3 LoA'],
        ];
    }

    public function provideInvalidLoaParameters()
    {
        return [
            ['1', 'https://vm.openconext.nl/loa1', 'The LoA level must be an integer value'],
            [null, 'https://vm.openconext.nl/loa1', 'The LoA level must be an integer value'],
            [false, 'https://vm.openconext.nl/loa1', 'The LoA level must be an integer value'],
            [1.5, 'https://vm.openconext.nl/loa1', 'The LoA level must be an integer value'],
            [[1], 'https://vm.openconext.nl/loa1', 'The LoA level must be an integer value'],
            [9999999999999999999999999999, 'https://vm.openconext.nl/loa1', 'The LoA level must be an integer value'],

            [new \stdClass(), 'https://vm.openconext.nl/loa1', 'The LoA level must be an integer value'],
            [0, 'https://vm.openconext.nl/loa1', 'Please provide a valid level. Accpetable LoA levels are "1, 2, 3"'],
            [-1, 'https://vm.openconext.nl/loa1', 'Please provide a valid level. Accpetable LoA levels are "1, 2, 3"'],
            [4, 'https://vm.openconext.nl/loa1', 'Please provide a valid level. Accpetable LoA levels are "1, 2, 3"'],

            [1, '', 'The LoA identifier must be of type string, and can not be empty'],
            [1, 1, 'The LoA identifier must be of type string, and can not be empty'],
            [1, -1, 'The LoA identifier must be of type string, and can not be empty'],
            [1, 1.1, 'The LoA identifier must be of type string, and can not be empty'],
            [1, false, 'The LoA identifier must be of type string, and can not be empty'],
            [1, true, 'The LoA identifier must be of type string, and can not be empty'],
            [1, ['https://vm.openconext.nl/loa1'], 'The LoA identifier must be of type string, and can not be empty'],
        ];
    }
}
