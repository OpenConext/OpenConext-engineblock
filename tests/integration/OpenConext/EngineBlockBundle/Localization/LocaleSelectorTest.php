<?php

namespace OpenConext\EngineBlockBundle\Localization;

use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;

class LocaleSelectorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function the_default_locale_should_be_returned_if_the_request_is_not_set()
    {
        $localeSelector = new LocaleSelector(['nl', 'en'], 'en');

        $this->assertSame('en', $localeSelector->getLocale());
    }

    /**
     * @test
     */
    public function the_locale_stored_in_the_cookie_should_be_used()
    {
        $localeSelector = new LocaleSelector(['nl', 'en'], 'en');

        $request = new Request([], [], [], ['lang' => 'nl']);

        $localeSelector->setRequest($request);

        $this->assertSame('nl', $localeSelector->getLocale());
    }

    /**
     * @test
     */
    public function the_locale_stored_in_the_cookie_should_be_ignored_if_it_is_not_one_of_the_available_locales()
    {
        $localeSelector = new LocaleSelector(['nl', 'en'], 'en');

        $request = new Request([], [], [], ['lang' => 'fr']);

        $localeSelector->setRequest($request);

        $this->assertSame('en', $localeSelector->getLocale());
    }

    /**
     * @test
     */
    public function the_accept_language_header_should_be_used_if_the_cookie_is_not_set()
    {
        $localeSelector = new LocaleSelector(['nl', 'en'], 'en');

        $request = new Request([], [], [], [], [], ['HTTP_ACCEPT_LANGUAGE' => 'nl-NL, en;q=0.8']);

        $localeSelector->setRequest($request);

        $this->assertSame('nl', $localeSelector->getLocale());
    }

    /**
     * @test
     */
    public function accepted_languages_that_are_not_available_should_be_ignored()
    {
        $localeSelector = new LocaleSelector(['nl', 'en'], 'en');

        $request = new Request([], [], [], [], [], ['HTTP_ACCEPT_LANGUAGE' => 'fr-FR, fr;q=0.9, nl;q=0.8']);

        $localeSelector->setRequest($request);

        $this->assertSame('nl', $localeSelector->getLocale());
    }

    /**
     * @test
     */
    public function the_default_language_should_be_used_if_neither_the_cookie_nor_the_accept_language_header_is_set()
    {
        $localeSelector = new LocaleSelector(['nl', 'en'], 'en');
        $localeSelector->setRequest(new Request());

        $this->assertSame('en', $localeSelector->getLocale());
    }
}
