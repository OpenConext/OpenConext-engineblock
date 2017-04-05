<?php

namespace OpenConext\EngineBlockBundle\Localization;

use Symfony\Component\HttpFoundation\Request;

final class LocaleSelector
{
    /**
     * @var string[]
     */
    private $availableLocales;

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @var Request|null
     */
    private $request;

    /**
     * @param string[] $availableLocales
     * @param string   $defaultLocale
     */
    public function __construct(array $availableLocales, $defaultLocale)
    {
        $this->availableLocales = $availableLocales;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @param Request $request
     *
     * @return void
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        if (!$this->request) {
            return $this->defaultLocale;
        }

        if (in_array($this->request->cookies->get('lang'), $this->availableLocales, true)) {
            return $this->request->cookies->get('lang');
        }

        // As the Request::getPreferredLanguage method works with an ordered array of available locales, the default
        // locale must be moved to the start of the array.
        $availableLocales = $this->availableLocales;
        array_unshift($availableLocales, $this->defaultLocale);
        $availableLocales = array_unique($availableLocales);

        return $this->request->getPreferredLanguage($availableLocales);
    }
}
