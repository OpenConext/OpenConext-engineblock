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

namespace OpenConext\EngineBlock\Validator;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Exception\InvalidRequestMethodException;
use OpenConext\EngineBlock\Exception\MissingParameterException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class AcsRequestValidatorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var AcsRequestValidator
     */
    private $validator;

    public function setUp(): void
    {
        $this->validator = new AcsRequestValidator();

        // PHPunit does not reset the superglobals on each run.
        $_GET = [];
        $_POST = [];
        $_SERVER = [];
    }

    /**
     * @backupGlobals enabled
     */
    public function test_happy_flow_get()
    {
        // Under the hood, the Binding::getCurrentBinding method is used, which directly reads from the super globals
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['SAMLResponse'] = 'loremipsum';

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->assertTrue($this->validator->isValid($request));
    }

    /**
     * @backupGlobals enabled
     */
    public function test_happy_flow_post()
    {
        // Under the hood, the Binding::getCurrentBinding method is used, which directly reads from the super globals
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['SAMLResponse'] = 'loremipsum';

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->assertTrue($this->validator->isValid($request));
    }

    /**
     * @backupGlobals enabled
     */
    public function test_patch_method_is_not_supported()
    {
        $this->expectException(InvalidRequestMethodException::class);
        $this->expectExceptionMessage('The HTTP request method "PATCH" is not supported on this SAML ACS endpoint');

        $_SERVER['REQUEST_METHOD'] = 'PATCH';

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->validator->isValid($request);
    }

    /**
     * @backupGlobals enabled
     */
    public function test_missing_saml_argument_on_post()
    {
        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('The parameter "SAMLResponse" is missing on the SAML ACS request');

        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->validator->isValid($request);
    }

    /**
     * @backupGlobals enabled
     */
    public function test_missing_saml_argument_on_get()
    {
        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('The parameter "SAMLResponse" is missing on the SAML ACS request');

        $_SERVER['REQUEST_METHOD'] = 'GET';

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->validator->isValid($request);
    }
}
