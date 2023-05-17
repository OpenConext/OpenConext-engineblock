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
use OpenConext\EngineBlock\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class UnsolicitedSsoRequestValidatorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var UnsolicitedSsoRequestValidator
     */
    private $validator;

    public function setUp(): void
    {
        $this->validator = new UnsolicitedSsoRequestValidator();

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
        $_GET['sp-entity-id'] = 'http://mock-sp';

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->assertTrue($this->validator->isValid($request));
    }

    /**
     * @backupGlobals enabled
     */
    public function test_post_is_not_allowed()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The HTTP request method "POST" is not supported on the IdP initiated SSO endpoint');

        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->validator->isValid($request);
    }

    /**
     * @backupGlobals enabled
     */
    public function test_put_binding_is_not_supported()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The HTTP request method "PUT" is not supported on the IdP initiated SSO endpoint');

        $_SERVER['REQUEST_METHOD'] = 'PUT';

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->validator->isValid($request);
    }

    /**
     * @backupGlobals enabled
     */
    public function test_malformed_argument()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The query parameter "sp-entity-id" is missing on the IdP initiated SSO request');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['sp-identity-id'] = 'http://mock-sp';

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->validator->isValid($request);
    }

    /**
     * @backupGlobals enabled
     */
    public function test_missing_argument()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The query parameter "sp-entity-id" is missing on the IdP initiated SSO request');

        $_SERVER['REQUEST_METHOD'] = 'GET';

        $request = new Request($_GET, $_POST, [], [], [], $_SERVER);

        $this->validator->isValid($request);
    }
}
