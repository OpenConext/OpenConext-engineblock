<?php

namespace OpenConext\EngineBlockBundle\Localization;

use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;

class LocaleProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function the_default_locale_should_be_returned_if_the_request_is_not_set()
    {
        $localeProvider = new LocaleProvider(['nl', 'en'], 'en');

        $this->assertSame('en', $localeProvider->getLocale());
    }

    /**
     * @test
     */
    public function the_lang_query_string_parameter_should_be_used()
    {
        $localeProvider = new LocaleProvider(['nl', 'en'], 'en');

        $request = new Request(['lang' => 'nl']);

        $localeProvider->scopeWithRequest($request);

        $this->assertSame('nl', $localeProvider->getLocale());
    }

    /**
     * @test
     */
    public function the_query_string_locale_should_be_ignored_if_it_is_not_available()
    {
        $localeProvider = new LocaleProvider(['nl', 'en'], 'en');

        $request = new Request(['lang' => 'fr']);

        $localeProvider->scopeWithRequest($request);

        $this->assertSame('en', $localeProvider->getLocale());
    }

    /**
     * @test
     */
    public function the_locale_in_the_request_body_should_be_used()
    {
        $localeProvider = new LocaleProvider(['nl', 'en'], 'en');

        $request = new Request([], ['lang' => 'nl']);

        $localeProvider->scopeWithRequest($request);

        $this->assertSame('nl', $localeProvider->getLocale());
    }

    /**
     * @test
     */
    public function the_request_body_locale_should_be_ignored_if_it_is_not_available()
    {
        $localeProvider = new LocaleProvider(['nl', 'en'], 'en');

        $request = new Request([], ['lang' => 'fr']);

        $localeProvider->scopeWithRequest($request);

        $this->assertSame('en', $localeProvider->getLocale());
    }

    /**
     * @test
     */
    public function the_locale_stored_in_the_cookie_should_be_used()
    {
        $localeProvider = new LocaleProvider(['nl', 'en'], 'en');

        $request = new Request([], [], [], ['lang' => 'nl']);

        $localeProvider->scopeWithRequest($request);

        $this->assertSame('nl', $localeProvider->getLocale());
    }

    /**
     * @test
     */
    public function the_locale_stored_in_the_cookie_should_be_ignored_if_it_is_not_one_of_the_available_locales()
    {
        $localeProvider = new LocaleProvider(['nl', 'en'], 'en');

        $request = new Request([], [], [], ['lang' => 'fr']);

        $localeProvider->scopeWithRequest($request);

        $this->assertSame('en', $localeProvider->getLocale());
    }

    /**
     * @test
     */
    public function the_accept_language_header_should_be_used_if_the_cookie_is_not_set()
    {
        $localeProvider = new LocaleProvider(['nl', 'en'], 'en');

        $request = new Request([], [], [], [], [], ['HTTP_ACCEPT_LANGUAGE' => 'nl-NL, en;q=0.8']);

        $localeProvider->scopeWithRequest($request);

        $this->assertSame('nl', $localeProvider->getLocale());
    }

    /**
     * @test
     */
    public function accepted_languages_that_are_not_available_should_be_ignored()
    {
        $localeProvider = new LocaleProvider(['nl', 'en'], 'en');

        $request = new Request([], [], [], [], [], ['HTTP_ACCEPT_LANGUAGE' => 'fr-FR, fr;q=0.9, nl;q=0.8']);

        $localeProvider->scopeWithRequest($request);

        $this->assertSame('nl', $localeProvider->getLocale());
    }

    /**
     * @test
     */
    public function the_default_language_should_be_used_if_neither_the_cookie_nor_the_accept_language_header_is_set()
    {
        $localeProvider = new LocaleProvider(['nl', 'en'], 'en');
        $localeProvider->scopeWithRequest(new Request());

        $this->assertSame('en', $localeProvider->getLocale());
    }
}
