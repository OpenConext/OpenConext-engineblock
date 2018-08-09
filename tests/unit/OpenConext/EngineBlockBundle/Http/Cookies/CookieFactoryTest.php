<?php

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
