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

use Symfony\Component\HttpFoundation\Request;

final class LocaleProvider
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
    public function scopeWithRequest(Request $request)
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

        if (in_array($this->request->query->get('lang'), $this->availableLocales, true)) {
            return $this->request->query->get('lang');
        }

        if (in_array($this->request->request->get('lang'), $this->availableLocales, true)) {
            return $this->request->request->get('lang');
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
