<?php

/**
 * Copyright 2019 SURFnet B.V.
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

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlockBundle\Sfo\SfoGatewayLoaMapping;
use PHPUnit_Framework_TestCase as TestCase;

class SfoGatewayLoaMappingTest extends TestCase
{
    /**
     * @test
     * @group Sfo
     */
    public function the_sfo_loa_mapping_object_should_be_successful_populated()
    {
        $sfoLoaMapping = new SfoGatewayLoaMapping([
            'manageLoa2' => 'gatewayLoa2',
            'manageLoa3' => 'gatewayLoa3',
        ],
            'gatewayLoa1'
        );

        $this->assertSame('gatewayLoa2', $sfoLoaMapping->transformToGatewayLoa('manageLoa2'));
        $this->assertSame('gatewayLoa2', $sfoLoaMapping->transformToGatewayLoa('manageLoa2'));
        $this->assertSame('gatewayLoa1', $sfoLoaMapping->getGatewayLoa1());
    }

    /**
     * @test
     * @group Sfo
     */
    public function the_sfo_loa_mapping_object_key_should_be_a_string_or_throw_an_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The sfo.gateway_loa_mapping configuration must be a map, key is not a string');

        $sfoLoaMapping = new SfoGatewayLoaMapping([
            5 => 'gatewayLoa2',
        ],
            'gatewayLoa1'
        );
    }

    /**
     * @test
     * @group Sfo
     */
    public function the_sfo_loa_mapping_object_value_should_be_a_string_or_throw_an_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The sfo.gateway_loa_mapping configuration must be a map, value is not a string');

        $sfoLoaMapping = new SfoGatewayLoaMapping([
            'manageLoa2' => 3,
        ],
            'gatewayLoa1'
        );
    }

    /**
     * @test
     * @group Sfo
     */
    public function the_sfo_loa_mapping_loa1_should_be_a_string_or_throw_an_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The sfo.gateway.loa.loa1 configuration must be a string');

        $sfoLoaMapping = new SfoGatewayLoaMapping([
            'manageLoa2' => 'gatewayLoa2',
        ],
            5
        );
    }
}
