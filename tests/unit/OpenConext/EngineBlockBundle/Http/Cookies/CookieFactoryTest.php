<?php

namespace OpenConext\EngineBlockBundle\Http\Cookies;

use DateTime as CoreDateTime;
use OpenConext\EngineBlock\DateTime\DateTime;
use OpenConext\EngineBlock\DateTimeHelper;
use PHPUnit_Framework_TestCase;

class CookieFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function returns_the_cookie()
    {
        DateTimeHelper::setCurrentTime(new DateTime(new CoreDateTime('@1')));

        $cookieFactory = new CookieFactory('name', '.example.com');

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
        $expiry = 3600;

        $cookieFactory = new CookieFactory('name', '.example.com', $expiry);

        $cookie = $cookieFactory->createCookie('value');

        // Allow the result to be off by one second, to compensate for time issues
        $this->assertSame(time() + $expiry, $cookie->getExpiresTime());
    }
}
