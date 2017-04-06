<?php

namespace OpenConext\EngineBlockBundle\Http\Cookies;

use PHPUnit_Framework_TestCase;

class CookieFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function returns_the_cookie()
    {
        $cookieFactory = new CookieFactory('name', '.example.com', 3600);

        $actual = $cookieFactory->createCookie('value');

        $this->assertSame('name', $actual->getName());
        $this->assertSame('value', $actual->getValue());
        $this->assertSame('.example.com', $actual->getDomain());
        $this->assertFalse($actual->isHttpOnly());
        $this->assertFalse($actual->isSecure());
    }
}
