<?php

/**
 * Copyright 2014 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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
    public function if_the_lang_query_string_is_set_it_should_be_used_to_determine_the_locale()
    {
        $localeProvider = new LocaleProvider(['nl', 'en'], 'en');

        $request = new Request(['lang' => 'nl']);

        $localeProvider->scopeWithRequest($request);

        $this->assertSame('nl', $localeProvider->getLocale());
    }

    /**
     * @test
     */
    public function the_lang_query_string_has_priority_over_the_request_body_cookie_and_accept_language_Header()
    {
        $localeProvider = new LocaleProvider(['nl', 'en'], 'en');

        $request = new Request(
            ['lang' => 'nl'],
            ['lang' => 'en'],
            [],
            ['lang' => 'en'],
            [],
            ['HTTP_ACCEPT_LANGUAGE' => 'en']
        );

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
    public function if_the_lang_query_string_is_not_set_the_locale_in_the_request_body_should_be_used()
    {
        $localeProvider = new LocaleProvider(['nl', 'en'], 'en');

        $request = new Request([], ['lang' => 'nl']);

        $localeProvider->scopeWithRequest($request);

        $this->assertSame('nl', $localeProvider->getLocale());
    }

    /**
     * @test
     */
    public function the_request_body_has_priority_over_the_cookie_and_accept_language_Header()
    {
        $localeProvider = new LocaleProvider(['nl', 'en'], 'en');

        $request = new Request(
            [],
            ['lang' => 'nl'],
            [],
            ['lang' => 'en'],
            [],
            ['HTTP_ACCEPT_LANGUAGE' => 'en']
        );

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
    public function the_locale_stored_in_the_cookie_should_be_used_if_the_query_string_and_request_body_do_not_contain_a_locale(
    )
    {
        $localeProvider = new LocaleProvider(['nl', 'en'], 'en');

        $request = new Request([], [], [], ['lang' => 'nl']);

        $localeProvider->scopeWithRequest($request);

        $this->assertSame('nl', $localeProvider->getLocale());
    }

    /**
     * @test
     */
    public function the_cookie_has_priority_over_the_accept_language_header()
    {
        $localeProvider = new LocaleProvider(['nl', 'en'], 'en');

        $request = new Request([], [], [], ['lang' => 'nl'], [], ['HTTP_ACCEPT_LANGUAGE' => 'en']);

        $localeProvider->scopeWithRequest($request);

        $this->assertSame('nl', $localeProvider->getLocale());
    }

    /**
     * @test
     */
    public function the_locale_stored_in_the_cookie_should_be_ignored_if_it_is_not_one_of_the_available_locales()
    {
        $localeProvider = new LocaleProvider(['nl', 'en'], 'en');

        $request = new Request([], [], [], ['lang' => 'fr'], [], ['HTTP_ACCEPT_LANGUAGE' => 'nl-NL, en;q=0.8']);

        $localeProvider->scopeWithRequest($request);

        $this->assertSame('nl', $localeProvider->getLocale());
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
    public function the_default_language_should_be_used_if_none_of_the_accepted_languages_are_available()
    {
        $localeProvider = new LocaleProvider(['nl', 'en'], 'en');

        $request = new Request([], [], [], [], [], ['HTTP_ACCEPT_LANGUAGE' => 'fr-FR, fr;q=0.9, de;q=0.8']);

        $localeProvider->scopeWithRequest($request);

        $this->assertSame('en', $localeProvider->getLocale());
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
