<?php

namespace OpenConext\EngineBlockBundle\Http\Cookies;

use DateInterval;
use DateTime;
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
    private $expiry;

    /**
     * @param string   $name
     * @param string   $domain
     * @param int|null $expiry
     */
    public function __construct($name, $domain, $expiry = null)
    {
        $this->name = $name;
        $this->domain = $domain;
        $this->expiry = $expiry;
    }

    /**
     * @param string $value
     *
     * @return Cookie
     */
    public function createCookie($value)
    {
        $expire = 0;

        if ($this->expiry) {
            $expire = (new DateTime())->add(DateInterval::createFromDateString($this->expiry . ' seconds'));
        }

        return new Cookie(
            $this->name,
            $value,
            $expire,
            '/',
            $this->domain,
            false,
            false
        );
    }
}
