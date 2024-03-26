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

namespace OpenConext\EngineBlockBundle\EventListener;

use OpenConext\EngineBlockBundle\Http\Cookies\CookieFactory;
use OpenConext\EngineBlockBundle\Localization\LocaleProvider;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

final class LocaleListener
{
    /**
     * @var LocaleProvider
     */
    private $localeProvider;

    /**
     * @var CookieFactory
     */
    private $cookieFactory;

    /**
     * @param LocaleProvider $localeProvider
     * @param CookieFactory  $cookieFactory
     */
    public function __construct(LocaleProvider $localeProvider, CookieFactory $cookieFactory)
    {
        $this->localeProvider = $localeProvider;
        $this->cookieFactory = $cookieFactory;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        $this->localeProvider->scopeWithRequest($request);

        $request->setLocale($this->localeProvider->getLocale());
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $cookie = $this->cookieFactory->createCookie($this->localeProvider->getLocale());
        $event->getResponse()->headers->setCookie($cookie);
    }
}
