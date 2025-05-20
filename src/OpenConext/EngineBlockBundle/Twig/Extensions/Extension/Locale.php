<?php

/**
 * Copyright 2010 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Twig\Extensions\Extension;

use OpenConext\EngineBlockBundle\Localization\LanguageSupportProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * The Locale extension can be used to retrieve the currently active locale. By default returns the locale that
 * can be found in the RequestStack. If none can be found in the request stack, the default locale is returned.
 */
class Locale extends AbstractExtension
{
    /**
     * @var string
     */
    private $locale;

    /**
     * @var null|Request
     */
    private $request;

    /**
     * @var LanguageSupportProvider
     */
    private $languageSupportProvider;

    public function __construct(
        RequestStack $requestStack,
        LanguageSupportProvider $languageSupportProvider,
        string $defaultLocale
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->locale = $this->retrieveLocale($requestStack, $defaultLocale);
        $this->languageSupportProvider = $languageSupportProvider;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('locale', [$this, 'getLocale']),
            new TwigFunction('postData', [$this, 'getPostData']),
            new TwigFunction('queryStringFor', [$this, 'getQueryStringFor']),
            new TwigFunction('supportedLocales', [$this, 'getSupportedLocales']),

        ];
    }

    public function getPostData(): array
    {
        $postArray = [];
        if ($this->request->isMethod(Request::METHOD_POST)) {
            $postArray = $this->request->request->all();
        }
        return $postArray;
    }

    /**
     * Reads the query string parameters, adds the lang parameter and returns the merged query string.
     * @param string $locale
     * @return string
     */
    public function getQueryStringFor(string $locale): string
    {
        $params = ['lang' => $locale];
        // re-create URL from GET parameters
        $params = array_merge(
            $this->request->query->all(),
            $params
        );

        $query = '';
        foreach ($params as $key => $value) {
            $query .= (strlen($query) == 0) ? '?' : '&' ;
            $query .= $key. '=' .urlencode($value);
        }

        return $query;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getSupportedLocales(): array
    {
        return $this->languageSupportProvider->getSupportedLanguages();
    }

    private function retrieveLocale(RequestStack $requestStack, string $defaultLocale): string
    {
        $currentRequest = $requestStack->getCurrentRequest();
        $locale = $defaultLocale;
        if ($currentRequest) {
            $locale = $currentRequest->getLocale();
        }
        return $locale;
    }
}
