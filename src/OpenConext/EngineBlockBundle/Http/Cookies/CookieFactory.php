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

use DateInterval;
use DateTimeImmutable;
use OpenConext\EngineBlock\Assert\Assertion;
use Symfony\Component\HttpFoundation\Cookie;

final class CookieFactory
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var int
     */
    private $expiryInSeconds;

    /**
     * @var bool
     */
    private $httpOnly;

    /**
     * @var bool
     */
    private $secure;

    /**
     * @var DateTimeImmutable
     */
    private $now;

    /**
     * @param string $name
     * @param string $domain
     * @param int|null $expiryInSeconds
     * @param bool $httpOnly
     * @param bool $secure
     * @param DateTimeImmutable|null $now
     * @throws \Assert\AssertionFailedException
     */
    public function __construct($name, $domain, $expiryInSeconds = null, $httpOnly = false, $secure = false, DateTimeImmutable $now = null)
    {
        Assertion::boolean($httpOnly);
        Assertion::boolean($secure);

        $this->name = $name;
        $this->domain = $domain;
        $this->expiryInSeconds = $expiryInSeconds;
        $this->httpOnly = $httpOnly;
        $this->secure = $secure;

        if (is_null($now)) {
            $now = new DateTimeImmutable();
        }
        $this->now = $now;
    }

    /**
     * @param string $value
     *
     * @return Cookie
     */
    public function createCookie($value)
    {
        $expiresAt = 0;

        if ($this->expiryInSeconds) {
            $expiresAt = $this->now->add(DateInterval::createFromDateString($this->expiryInSeconds . ' seconds'));
        }

        return new Cookie(
            $this->name,
            $value,
            $expiresAt,
            '/',
            $this->domain,
            $this->secure,
            $this->httpOnly
        );
    }
}
