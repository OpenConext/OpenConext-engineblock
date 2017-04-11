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
     * @param string   $name
     * @param string   $domain
     * @param int|null $expiryInSeconds
     * @param bool     $httpOnly
     * @param bool     $secure
     */
    public function __construct($name, $domain, $expiryInSeconds = null, $httpOnly = false, $secure = false)
    {
        Assertion::boolean($httpOnly);
        Assertion::boolean($secure);

        $this->name = $name;
        $this->domain = $domain;
        $this->expiryInSeconds = $expiryInSeconds;
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
        $expiresAt = 0;

        if ($this->expiryInSeconds) {
            $expiresAt = (new DateTime())->add(DateInterval::createFromDateString($this->expiryInSeconds . ' seconds'));
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
