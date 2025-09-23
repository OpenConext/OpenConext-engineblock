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

use PHPUnit\Framework\TestCase;

class MfaEntityFactoryTest extends TestCase
{
    public function test_it_distinguishes_between_regular_and_transparent_entities()
    {
        $this->assertInstanceOf(TransparentMfaEntity::class, MfaEntityFactory::from('https://example.com', 'transparent_authn_context'));
        $this->assertInstanceOf(MfaEntity::class, MfaEntityFactory::from('https://example.com', 'http://schemas.microsoft.com/claims/multipleauthn'));
    }

    public function test_it_distinguishes_between_regular_and_transparent_entities_from_json()
    {
        $data = [
            'entityId' => 'https://example.com',
            'level' => 'transparent_authn_context',
        ];
        $this->assertInstanceOf(TransparentMfaEntity::class, MfaEntityFactory::fromJson($data));
        $data['level'] = 'http://schemas.microsoft.com/claims/multipleauthn';
        $this->assertInstanceOf(MfaEntity::class, MfaEntityFactory::fromJson($data));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideInvalidJsonData')]
    public function test_from_json_factory_method_performs_input_validation($data, $expectedMessage)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        MfaEntityFactory::fromJson($data);
    }

    public static function provideInvalidJsonData()
    {
        yield [['entityId' => null, 'level' => 'transparent_authn_context'], 'MFA entityId must be of type string'];
        yield [['entityId' => 0, 'level' => 'transparent_authn_context'], 'MFA entityId must be of type string'];
        yield [['entityId' => true, 'level' => 'transparent_authn_context'], 'MFA entityId must be of type string'];
        yield [['entityIde' => true, 'level' => 'transparent_authn_context'], 'MFA entityId must be specified'];
        yield [['level' => 'transparent_authn_context'], 'MFA entityId must be specified'];
        yield [['entityId' => 'https://example.com', 'level' => 0], 'MFA level must be of type string'];
        yield [['entityId' => 'https://example.com', 'level' => false], 'MFA level must be of type string'];
        yield [['entityId' => 'https://example.com', 'level' => null], 'MFA level must be of type string'];
        yield [['entityId'=> 'https://example.com'], 'MFA entity level must be specified'];
    }
}
