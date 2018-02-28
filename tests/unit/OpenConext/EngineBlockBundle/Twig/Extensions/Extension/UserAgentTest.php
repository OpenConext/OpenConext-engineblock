<?php

/**
 * Copyright 2018 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Twig\Extensions\Extension;

use Mockery as m;
use OpenConext\EngineBlockBundle\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ServerBag;

class UserAgentTest extends TestCase
{
    public function test_get_user_agent()
    {
        $this->assertEquals('safari5', $this->buildUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.59.10 (KHTML, like Gecko) Version/5.1.9 Safari/534.59.10')->userAgent('safari'));
        $this->assertEquals('safari10', $this->buildUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8')->userAgent('safari'));
    }

    public function test_get_user_agent_empty_when_no_match()
    {
        $this->assertEmpty($this->buildUserAgent('Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:58.0) Gecko/20100101 Firefox/58.0')->userAgent('safari'));
    }

    public function test_get_user_agent_empty_when_no_request()
    {
        $this->assertEmpty($this->buildUserAgent('', true)->userAgent('safari'));
    }

    public function test_get_user_agent_exception_on_unsupported()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This user agent ("gecko") is not yet supported in the UserAgent Twig extension');
        $this->assertEmpty($this->buildUserAgent('Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:58.0) Gecko/20100101 Firefox/58.0')->userAgent('gecko'));
    }

    private function buildUserAgent($expectedUserAgent, $unsetCurrentRequest = false)
    {
        $requestStack = m::mock(RequestStack::class);

        if ($unsetCurrentRequest) {
            $requestStack->shouldReceive('getCurrentRequest')->andReturn(null);
        } else {
            $serverBag = m::mock(ServerBag::class);
            $serverBag->shouldReceive('get')
                ->with('HTTP_USER_AGENT', '')
                ->andReturn($expectedUserAgent);

            $request = m::mock(Request::class);
            $request->server = $serverBag;
            $requestStack->shouldReceive('getCurrentRequest')->andReturn($request);
        }
        return new UserAgent($requestStack);
    }
}
