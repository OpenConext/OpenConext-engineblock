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

namespace OpenConext\EngineBlockBundle\Tests;

use Exception;
use PHPUnit_Framework_TestCase as TestCase;
use RuntimeException;
use OpenConext\EngineBlockBundle\Exception\Art;

class ArtTest extends TestCase
{
    /**
     * @test
     * @group art
     */
    public function art_code_is_numeric()
    {
        $this->assertTrue(is_numeric(Art::forException(new Exception)), 'Expected numeric Art code');
    }

    /**
     * @test
     * @group art
     */
    public function art_code_is_distinct_per_exception_type()
    {
        $art1 = new Exception();
        $art2 = new RuntimeException();

        $this->assertNotEquals($art1, $art2, 'Expected different art code for different exception type');
    }

    /**
     * @test
     * @group art
     */
    public function art_code_is_distinct_per_message()
    {
        $art1 = new Exception('one');
        $art2 = new Exception('two');

        $this->assertNotEquals($art1, $art2, 'Expected different art code for different exception message');
    }

    /**
     * @test
     * @group art
     * @dataProvider artCodeWithStrippedVariables
     *
     * @param Exception $exception
     * @param int $expectedArtCode
     */
    public function exception_translates_to_art_code_with_variables_stripped(Exception $exception, $expectedArtCode)
    {
        $this->assertEquals(
            $expectedArtCode,
            Art::forException($exception)
        );
    }

    public function artCodeWithStrippedVariables()
    {
        $artCode = Art::forException(
            new Exception('This is a \'good\' message')
        );

        return [
            [new Exception('This is a \'nice\' message'), $artCode],
            [new Exception('This is a "real" message'), $artCode],
            [new Exception('This is a "re"X"al" message'), $artCode],
            [new Exception('This is a "re\'X\'al" message'), $artCode],
            [new Exception('This is a "" message'), $artCode],
        ];
    }
}
