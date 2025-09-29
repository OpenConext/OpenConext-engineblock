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

use PHPUnit\Framework\TestCase;

class EngineBlock_Test_TypeTest extends TestCase
{
    /**
     *
     * @param $attributeName
     * @param $options
     * @param $attributes
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('validAttributesProvider')]
    public function testAttributeValidates($attributeName, $options, $attributes)
    {
        $validator = new EngineBlock_Attributes_Validator_Type($attributeName, $options);
        $this->assertTrue($validator->validate($attributes));
    }

    public static function validAttributesProvider()
    {
        return array(
            array(
                'attributeName' => 'foo',
                'options' => 'URN',
                'attributes' => array(
                    'foo' => array(
                        'urn:mace:dir:entitlement:common-lib-terms'
                    )
                )
            ),
            array(
                'attributeName' => 'foo',
                'options' => 'URL',
                'attributes' => array(
                    'foo' => array(
                        'http://example.com'
                    )
                )
            ),
            array(
                'attributeName' => 'foo',
                'options' => 'URI',
                'attributes' => array(
                    'foo' => array(
                        '?'
                    )
                )
            ),
            array(
                'attributeName' => 'foo',
                'options' => 'HostName',
                'attributes' => array(
                    'foo' => array(
                        'example',
                        'example.org',
                        'test.example.org',
                        'test-test.example.org',
                        'test-test.example',
                    )
                )
            )
        );
    }
}
