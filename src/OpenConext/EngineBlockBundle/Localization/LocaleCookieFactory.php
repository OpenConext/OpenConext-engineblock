<?php

namespace OpenConext\EngineBlockBundle\Localization;

use DateInterval;
use DateTime;
use Symfony\Component\HttpFoundation\Cookie;

final class LocaleCookieFactory
{
    /**
     * @var string
     */
    private $domain;

    /**
     * @var int
     */
    private $expiry;

    /**
     * @param string $domain
     * @param int    $expiry
     */
    public function __construct($domain, $expiry)
    {
        $this->domain = $domain;
        $this->expiry = $expiry;
    }

    /**
     * Creates a cookie that stores the language preference of the user.
     *
     * For compatibility reasons with the rest of the platform, the domain is set, and the secure and httpOnly flags are
     * disabled.
     *
     * @param string $locale
     *
     * @return Cookie
     */
    public function createCookie($locale)
    {
        $expirationDateTime = (new DateTime())->add(DateInterval::createFromDateString($this->expiry . ' seconds'));

        return new Cookie(
            'lang',
            $locale,
            $expirationDateTime,
            '/',
            $this->domain,
            false,
            false
        );
    }
}
