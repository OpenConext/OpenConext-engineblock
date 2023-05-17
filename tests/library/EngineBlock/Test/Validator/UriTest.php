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

/**
 * @todo write test which tests failing...this validator is so permissive it is VERY hard to let it fail
 */
class EngineBlock_Test_Validator_UriTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var EngineBlock_Validator_Urn
     */
    private $validator;

    public function setUp(): void
    {
        $this->validator = new EngineBlock_Validator_Uri();
    }

    /**
     * @dataProvider validUriProvider
     */
    public function testUriValidates($uri)
    {
        $this->assertTrue($this->validator->validate($uri));
    }

    public function validUriProvider()
    {
        return array(
            array('http://example.com'), // Pretty standard http url
            array('urn:mace:dir:entitlement:common-lib-terms') // Saml entitlement
        );
    }
}
