<?php

/**
 * Copyright 2017 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Tests\AttributeAggregation\Dto;

use InvalidArgumentException;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\AggregatedAttribute;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\Response;
use OpenConext\EngineBlockBundle\Exception\InvalidAttributeAggregationResponseException;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @group AttributeAggregation
 */
class ResponseTest extends TestCase
{
    /**
     * @test
     */
    public function response_contains_parsed_aggregated_attributes()
    {
        $response = Response::fromData([
          [
            'name' => 'name',
            'values' => [],
            'source' => 'voot',
          ],
          [
            'name' => 'name',
            'values' => [],
            'source' => 'voot',
          ]
        ]);

        $this->assertCount(2, $response->attributes);
        $this->assertInstanceOf(AggregatedAttribute::class, $response->attributes[0]);
    }

    /**
     * @test
     */
    public function response_data_must_be_an_array()
    {
        $this->setExpectedException(InvalidAttributeAggregationResponseException::class);
        Response::fromData(NULL);
    }

    /**
     * @test
     */
    public function response_attribute_must_have_a_name()
    {
        $this->setExpectedException(InvalidAttributeAggregationResponseException::class);
        Response::fromData([
          [
            'values' => [],
            'source' => 'voot',
          ]
        ]);
    }

    /**
     * @test
     */
    public function response_attribute_must_have_values_property()
    {
        $this->setExpectedException(InvalidAttributeAggregationResponseException::class);
        Response::fromData([
          [
            'name' => 'name',
            'source' => 'voot',
          ]
        ]);
    }

    /**
     * @test
     */
    public function response_attribute_must_have_a_source()
    {
        $this->setExpectedException(InvalidAttributeAggregationResponseException::class);
        Response::fromData([
          [
            'name' => 'name',
            'values' => [],
          ]
        ]);
    }
}
