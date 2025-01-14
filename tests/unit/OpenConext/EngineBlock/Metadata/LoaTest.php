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
use TypeError;

class LoaTest extends TestCase
{
    /**
     * @dataProvider provideValidLoaParameters
     */
    public function test_loa_happy_flow(int $level, string $identifier, $expectationDescription)
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
    /**
     * @dataProvider provideErrorneousLoaParameters
     */
    public function test_loa_errors($level, $identifier)
    {
        $this->expectException(TypeError::class);
        Loa::create($level, $identifier);
    }

    public function test_loa_can_be_compared_to_other_loa()
    {
        $loa1 = Loa::create(10, 'https://dev.openconext.local/assurance/loa1');
        $loa15 = Loa::create(15, 'https://dev.openconext.local/assurance/loa1_5');
        $loa2 = Loa::create(20, 'https://dev.openconext.local/assurance/loa2');
        $loa3 = Loa::create(30, 'https://dev.openconext.local/assurance/loa3');

        $this->assertTrue($loa3->levelIsHigherOrEqualTo($loa3));
        $this->assertTrue($loa3->levelIsHigherOrEqualTo($loa2));
        $this->assertTrue($loa3->levelIsHigherOrEqualTo($loa1));

        $this->assertTrue($loa2->levelIsHigherOrEqualTo($loa2));
        $this->assertTrue($loa2->levelIsHigherOrEqualTo($loa1));
        $this->assertFalse($loa2->levelIsHigherOrEqualTo($loa3));

        $this->assertTrue($loa15->levelIsHigherOrEqualTo($loa1));
        $this->assertTrue($loa15->levelIsHigherOrEqualTo($loa15));
        $this->assertFalse($loa15->levelIsHigherOrEqualTo($loa2));
        $this->assertFalse($loa15->levelIsHigherOrEqualTo($loa3));

        $this->assertTrue($loa1->levelIsHigherOrEqualTo($loa1));
        $this->assertFalse($loa1->levelIsHigherOrEqualTo($loa2));
        $this->assertFalse($loa1->levelIsHigherOrEqualTo($loa3));
    }

    public function provideValidLoaParameters()
    {
        return [
            [10, 'https://vm.openconext.nl/loa1', 'A level 1 LoA'],
            [15, 'https://vm.openconext.nl/loa1_5', 'A level 1.5 LoA'],
            [20, 'https://vm.openconext.nl/loa2', 'A level 2 LoA'],
            [30, 'https://vm.openconext.nl/loa3', 'A level 3 LoA'],
        ];
    }

    public function provideInvalidLoaParameters()
    {
        return [
            [0, 'https://vm.openconext.nl/loa1', 'Please provide a valid level. Acceptable LoA levels are "10, 15, 20, 30"'],
            [-1, 'https://vm.openconext.nl/loa1', 'Please provide a valid level. Acceptable LoA levels are "10, 15, 20, 30"'],
            [4, 'https://vm.openconext.nl/loa1', 'Please provide a valid level. Acceptable LoA levels are "10, 15, 20, 30"'],

            [10, '', 'The LoA identifier must be of type string, and can not be empty'],
        ];
    }

    public function provideErrorneousLoaParameters()
    {
        return [
            [9999999999999999999999999999, 'https://vm.openconext.nl/loa1', 'The LoA level must be an integer value'],
            [new \stdClass(), 'https://vm.openconext.nl/loa1', 'The LoA level must be an integer value'],
            [10, ['https://vm.openconext.nl/loa1'], 'The LoA identifier must be of type string, and can not be empty'],
        ];
    }
}
