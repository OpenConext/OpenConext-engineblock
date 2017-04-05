<?php

namespace OpenConext\EngineBlockBundle\EventListener;

use OpenConext\EngineBlockBundle\Localization\LocaleCookieFactory;
use OpenConext\EngineBlockBundle\Localization\LocaleSelector;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

final class LocaleListener
{
    /**
     * @var LocaleSelector
     */
    private $localeSelector;

    /**
     * @var LocaleCookieFactory
     */
    private $cookieFactory;

    /**
     * @param LocaleSelector      $localeSelector
     * @param LocaleCookieFactory $cookieFactory
     */
    public function __construct(LocaleSelector $localeSelector, LocaleCookieFactory $cookieFactory)
    {
        $this->localeSelector = $localeSelector;
        $this->cookieFactory = $cookieFactory;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $this->localeSelector->setRequest($request);

        $request->setLocale($this->localeSelector->getLocale());
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->query->has('lang') || $request->request->has('lang')) {
            $cookie = $this->cookieFactory->createCookie($this->localeSelector->getLocale());
            $event->getResponse()->headers->setCookie($cookie);
        }
    }
}
