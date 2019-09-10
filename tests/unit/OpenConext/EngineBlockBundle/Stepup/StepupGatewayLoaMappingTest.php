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

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlock\Exception\RuntimeException;
use OpenConext\EngineBlockBundle\Stepup\StepupGatewayLoaMapping;
use PHPUnit_Framework_TestCase as TestCase;

class StepupGatewayLoaMappingTest extends TestCase
{
    /**
     * @test
     * @group Stepup
     */
    public function the_stepup_loa_mapping_object_should_be_successful_populated()
    {
        $stepupLoaMapping = new StepupGatewayLoaMapping([
            'ebLoa2' => 'gatewayLoa2',
            'ebLoa3' => 'gatewayLoa3',
        ],
            'gatewayLoa1'
        );

        $this->assertSame('gatewayLoa2', $stepupLoaMapping->transformToGatewayLoa('ebLoa2'));
        $this->assertSame('gatewayLoa2', $stepupLoaMapping->transformToGatewayLoa('ebLoa2'));
        $this->assertSame('gatewayLoa1', $stepupLoaMapping->getGatewayLoa1());
    }

    /**
     * @test
     * @group Stepup
     */
    public function the_stepup_loa_mapping_object_key_should_be_a_string_or_throw_an_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The stepup.gateway_loa_mapping configuration must be a map, key is not a string');

        $stepupLoaMapping = new StepupGatewayLoaMapping([
            5 => 'gatewayLoa2',
        ],
            'gatewayLoa1'
        );
    }

    /**
     * @test
     * @group Stepup
     */
    public function the_stepup_loa_mapping_object_value_should_be_a_string_or_throw_an_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The stepup.gateway_loa_mapping configuration must be a map, value is not a string');

        $stepupLoaMapping = new StepupGatewayLoaMapping([
            'ebLoa2' => 3,
        ],
            'gatewayLoa1'
        );
    }

    /**
     * @test
     * @group Stepup
     */
    public function the_stepup_loa_mapping_loa1_should_be_a_string_or_throw_an_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The stepup.gateway.loa.loa1 configuration must be a string');

        $stepupLoaMapping = new StepupGatewayLoaMapping([
            'ebLoa2' => 'gatewayLoa2',
        ],
            5
        );
    }

    /**
     * @test
     * @group Stepup
     */
    public function the_stepup_loa_mapping_object_should_successful_map_back()
    {
        $stepupLoaMapping = new StepupGatewayLoaMapping([
            'ebLoa2' => 'gatewayLoa2',
            'ebLoa3' => 'gatewayLoa3',
        ],
            'gatewayLoa1'
        );

        $this->assertSame('ebLoa2', $stepupLoaMapping->transformToEbLoa('gatewayLoa2'));
        $this->assertSame('ebLoa3', $stepupLoaMapping->transformToEbLoa('gatewayLoa3'));
    }

    /**
     * @test
     * @group Stepup
     */
    public function the_stepup_loa_mapping_object_should_return_an_exception_when_unable_to_map_back()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to find the received stepup LoA in the configured EngineBlock LoA');

        $stepupLoaMapping = new StepupGatewayLoaMapping([
            'ebLoa2' => 'gatewayLoa2',
        ],
            'gatewayLoa1'
        );

        $stepupLoaMapping->transformToEbLoa('gatewayLoa3');
    }
}
