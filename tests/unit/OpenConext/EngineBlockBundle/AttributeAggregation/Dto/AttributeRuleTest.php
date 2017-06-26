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
use OpenConext\Component\EngineBlockMetadata\AttributeReleasePolicy;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\AttributeRule;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\Request;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @group AttributeAggregation
 */
class AttributeRuleTest extends TestCase
{
    /**
     * @test
     */
    public function rule_name_must_be_set()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        AttributeRule::from(null, 'value', 'source');
    }

    /**
     * @test
     */
    public function rule_value_must_be_set()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        AttributeRule::from('name', null, 'source');
    }

    /**
     * @test
     */
    public function rule_source_must_be_set()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        AttributeRule::from('name', 'value', null);
    }

    /**
     * @test
     */
    public function rules_are_created_from_arp()
    {
        $rules = AttributeRule::fromArp(
            new AttributeReleasePolicy([
                'name' => [
                    [
                        'value' => 'value',
                        'source' => 'source',
                    ],
                ],
            ])
        );

        $this->assertCount(1, $rules);
        $this->assertInstanceOf(AttributeRule::class, $rules[0]);
        $this->assertEquals($rules[0]->name, 'name');
        $this->assertEquals($rules[0]->value, 'value');
        $this->assertEquals($rules[0]->source, 'source');
    }
}
