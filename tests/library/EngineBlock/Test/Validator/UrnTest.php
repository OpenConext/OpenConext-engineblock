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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class EngineBlock_Test_Validator_UrnTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var EngineBlock_Validator_Urn
     */
    private $validator;

    public function setUp(): void
    {
        $this->validator = new EngineBlock_Validator_Urn();
    }

    /**
     * @dataProvider validUrnProvider
     */
    public function testUrnValidates($urn)
    {
        $this->assertTrue($this->validator->validate($urn));
    }

    /**
     * @dataProvider invalidUrnProvider
     */
    public function testUrnValidationFails($invalidUrn)
    {
        $this->assertFalse($this->validator->validate($invalidUrn));
    }

    public function validUrnProvider()
    {
        yield ['urn:collab:person:example.org:jdoe'];
        yield ['urn:mace:dir:entitlement:common-lib-terms'];
        yield ['urn:mace:terena.org:tcs:personal-user'];
        yield ['urn:oid:1.3.6.1.4.1.5923.1.1.1.5'];
        yield ['urn:mace:dir:attribute-def:eduPersonPrincipalName'];
        yield ['urn:x-example:example'];
        yield ['urn:mace:surf.nl:voorbeeld'];
        yield ['urn:group:team#fragment'];
    }

    public function invalidUrnProvider()
    {
        yield ['abcdefg'];
        yield ['urn:collab:%0'];
        yield ['urn:org.openconext.licenseInfo'];
        yield ['foo:bar:baz'];
        yield ['urn:f:bar'];
        yield ["\nurn:mace:dir:attribute-def:eduPersonPrincipalName"];
        yield ["urn:mace:dir:attribute-def:eduPersonPrincipalName\n"];
        yield [' urn:collab:person:example.org:jdoe'];
        yield ['urn:collab:person:example.org:jdoe '];
        yield ['urn:collab:person:example org:jdoe'];
        yield ["urn:collab:person:example.org:jdoe\nurn:collab:person:example.org:jdoe"];
        yield ['urn:collab:person:example.org:jdoe, urn:collab:person:example.org:jdoe'];
        yield ['https://example.org/url'];
    }
}
