<?php

namespace OpenConext\EngineBlockBundle\Http\Cookies;

use PHPUnit_Framework_TestCase;

class LocaleCookieFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function returns_the_cookie()
    {
        $cookieFactory = new LocaleCookieFactory('.example.com', 3600);

        $actual = $cookieFactory->createCookie('nl');

        $this->assertSame('lang', $actual->getName());
        $this->assertSame('nl', $actual->getValue());
        $this->assertSame('.example.com', $actual->getDomain());
        $this->assertFalse($actual->isHttpOnly());
        $this->assertFalse($actual->isSecure());
    }
}
