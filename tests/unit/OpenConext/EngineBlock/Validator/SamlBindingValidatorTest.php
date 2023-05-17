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

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Exception\InvalidBindingException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class SamlBindingValidatorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var SamlBindingValidator
     */
    private $validator;

    public function setUp(): void
    {
        $this->validator = new SamlBindingValidator();

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
        $_GET['SAMLRequest'] = 'loremipsum';

        $request = m::mock(Request::class);
        $request
            ->shouldReceive('getMethod')
            ->andReturn(Request::METHOD_GET);
        $this->assertTrue($this->validator->isValid($request));
    }

    /**
     * @backupGlobals enabled
     */
    public function test_happy_flow_post()
    {
        // Under the hood, the Binding::getCurrentBinding method is used, which directly reads from the super globals
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['SAMLRequest'] = 'loremipsum';

        $request = m::mock(Request::class);
        $request
            ->shouldReceive('getMethod')
            ->andReturn(Request::METHOD_POST);
        $this->assertTrue($this->validator->isValid($request));
    }

    /**
     * @backupGlobals enabled
     */
    public function test_post_binding_is_not_supported()
    {
        $this->expectException(InvalidBindingException::class);
        $this->expectExceptionMessage('No SAMLRequest or SAMLResponse parameter was found in the HTTP');

        $request = m::mock(Request::class);
        $request
            ->shouldReceive('getMethod')
            ->andReturn(Request::METHOD_PATCH);

        $this->validator->isValid($request);
    }

    /**
     * @backupGlobals enabled
     */
    public function test_used_invalid_binding()
    {
        $this->expectException(InvalidBindingException::class);
        $this->expectExceptionMessage('No SAMLRequest or SAMLResponse parameter was found in the HTTP');

        // Under the hood, the Binding::getCurrentBinding method is used, which directly reads from the super globals
        $_SERVER['REQUEST_METHOD'] = 'GET';
        // Even though we are handling a AuthNRequest, the binding is based on a get parameter named SAMLRequest
        $_GET['AuhtnRequest'] = 'loremipsum';

        $request = m::mock(Request::class);
        $request
            ->shouldReceive('getMethod')
            ->andReturn(Request::METHOD_GET);

        $this->validator->isValid($request);
    }

    /**
     * @backupGlobals enabled
     */
    public function test_used_unsupported_binding()
    {
        $this->expectException(InvalidBindingException::class);
        $this->expectExceptionMessage('The binding type "SAML2\HTTPArtifact" is not supported on this endpoint');

        // Under the hood, the Binding::getCurrentBinding method is used, which directly reads from the super globals
        $_SERVER['REQUEST_METHOD'] = 'GET';
        // EB does not support artifact binding
        $_GET['SAMLart'] = 'loremipsum';

        $request = m::mock(Request::class);
        $request
            ->shouldReceive('getMethod')
            ->andReturn(Request::METHOD_GET);

        $this->validator->isValid($request);
    }
}
