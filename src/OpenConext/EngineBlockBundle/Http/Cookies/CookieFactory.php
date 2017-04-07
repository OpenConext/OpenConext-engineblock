<?php

namespace OpenConext\EngineBlockBundle\Http\Cookies;

use DateInterval;
use DateTime;
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
    private $expiry;

    /**
     * @var bool
     */
    private $httpOnly;

    /**
     * @var bool
     */
    private $secure;

    /**
     * @param string   $name
     * @param string   $domain
     * @param int|null $expiry
     * @param bool     $httpOnly
     * @param bool     $secure
     */
    public function __construct($name, $domain, $expiry = null, $httpOnly = false, $secure = false)
    {
        Assertion::boolean($httpOnly);
        Assertion::boolean($secure);

        $this->name = $name;
        $this->domain = $domain;
        $this->expiry = $expiry;
        $this->httpOnly = $httpOnly;
        $this->secure = $secure;
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
            $this->secure,
            $this->httpOnly
        );
    }
}
