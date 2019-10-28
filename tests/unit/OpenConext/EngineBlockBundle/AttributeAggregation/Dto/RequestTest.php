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

namespace OpenConext\EngineBlockBundle\Tests\AttributeAggregation\Dto;

use InvalidArgumentException;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\AttributeRule;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\Request;
use PHPUnit\Framework\TestCase;

/**
 * @group AttributeAggregation
 */
class RequestTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @test
     */
    public function request_serializes_to_aa_api_format()
    {
        $request = Request::from(
            'sp-entity-id',
            'idp-entity-id',
            'subject',
            [
                'attr' => ['1', '2', '3'],
            ],
            [
                AttributeRule::from('name', 'value', 'source'),
                AttributeRule::from('name', 'value', 'source'),
            ]
        );

        $expectedJson = json_encode(
            [
                'userAttributes' => [
                    [
                        'name' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
                        'values' => ['subject'],
                    ],
                    [
                        'name' => 'SPentityID',
                        'values' => ['sp-entity-id'],
                    ],
                    [
                        'name' => 'attr',
                        'values' => ['1', '2', '3'],
                    ],
                ],
                'arpAttributes' => [
                    'name' => [
                        [
                            'value' => 'value',
                            'source' => 'source',
                        ],
                        [
                            'value' => 'value',
                            'source' => 'source',
                        ],
                    ],
                ],
            ],
            JSON_PRETTY_PRINT
        );

        $actualJson = json_encode($request, JSON_PRETTY_PRINT);

        $this->assertEquals($expectedJson, $actualJson);
    }

    /**
     * @test
     */
    public function request_serializes_to_aa_api_format_filters_non_string_values()
    {
        $request = Request::from(
            'sp-entity-id',
            'idp-entity-id',
            'subject',
            [
                'attr' => ['1', ['foo' => 'bar'], '3'],
            ],
            [
                AttributeRule::from('name', 'value', 'source'),
                AttributeRule::from('name', 'value', 'source'),
            ]
        );

        $expectedJson = json_encode(
            [
                'userAttributes' => [
                    [
                        'name' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
                        'values' => ['subject'],
                    ],
                    [
                        'name' => 'SPentityID',
                        'values' => ['sp-entity-id'],
                    ],
                ],
                'arpAttributes' => [
                    'name' => [
                        [
                            'value' => 'value',
                            'source' => 'source',
                        ],
                        [
                            'value' => 'value',
                            'source' => 'source',
                        ],
                    ],
                ],
            ],
            JSON_PRETTY_PRINT
        );

        $actualJson = json_encode($request, JSON_PRETTY_PRINT);

        $this->assertEquals($expectedJson, $actualJson);
    }

    /**
     * @test
     */
    public function request_subject_must_be_set()
    {
        $this->expectException(InvalidArgumentException::class);
        Request::from('sp-entity-id', NULL, [], []);
    }

    /**
     * @test
     */
    public function request_sp_entity_id_must_be_set()
    {
        $this->expectException(InvalidArgumentException::class);
        Request::from(NULL, 'idp-entity-id', 'subject-id', [], []);
    }

    /**
     * @test
     */
    public function request_idp_entity_id_must_be_set()
    {
        $this->expectException(InvalidArgumentException::class);
        Request::from('sp-entity-id', 'idp-entity-id', NULL, 'subject-id', [], []);
    }

    /**
     * @test
     */
    public function request_attributes_must_be_of_type_dto()
    {
        $this->expectException(InvalidArgumentException::class);
        Request::from('sp-entity-id', 'idp-entity-id', 'subject', [], [['invalid']]);
    }
}
