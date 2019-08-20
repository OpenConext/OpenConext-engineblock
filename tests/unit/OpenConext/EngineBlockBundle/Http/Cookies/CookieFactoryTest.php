<?php

/**
 * Copyright 2014 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Http\Cookies;

use DateTimeImmutable;
use PHPUnit_Framework_TestCase;

class CookieFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function returns_the_cookie()
    {
        $now = new DateTimeImmutable();

        $cookieFactory = new CookieFactory('name', '.example.com', null, false, false, $now);

        $actual = $cookieFactory->createCookie('value');

        $this->assertSame('name', $actual->getName());
        $this->assertSame('value', $actual->getValue());
        $this->assertSame('.example.com', $actual->getDomain());
        $this->assertSame(0, $actual->getExpiresTime());
        $this->assertFalse($actual->isHttpOnly());
        $this->assertFalse($actual->isSecure());
    }

    /**
     * @test
     */
    public function sets_the_expiry_time()
    {
        $now = new DateTimeImmutable();
        $expiry = 3600;

        $cookieFactory = new CookieFactory('name', '.example.com', $expiry, false, false, $now);

        $cookie = $cookieFactory->createCookie('value');

        // Allow the result to be off by one second, to compensate for time issues
        $this->assertSame($now->getTimestamp() + $expiry, $cookie->getExpiresTime());
    }
}
