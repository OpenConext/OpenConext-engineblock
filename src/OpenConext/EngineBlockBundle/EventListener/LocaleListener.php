<?php

namespace OpenConext\EngineBlockBundle\EventListener;

use OpenConext\EngineBlockBundle\Http\Cookies\CookieFactory;
use OpenConext\EngineBlockBundle\Localization\LocaleProvider;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

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

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $this->localeProvider->scopeWithRequest($request);

        $request->setLocale($this->localeProvider->getLocale());
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $cookie = $this->cookieFactory->createCookie($this->localeProvider->getLocale());
        $event->getResponse()->headers->setCookie($cookie);
    }
}
