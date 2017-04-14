<?php

namespace OpenConext\EngineBlockBridge\EventListener;

use EngineBlock_ApplicationSingleton;
use OpenConext\EngineBlockBundle\Localization\LocaleProvider;
use Zend_Translate_Adapter;

/**
 * This listener depends on the LocaleListener in the EngineBlockBundle (which has to scope the LocaleProvider with the
 * current request), so the priority should be set so that it is called after the LocaleListener. Otherwise, if the
 * LocaleProvider hasn't been scoped with the request yet, it will fall back to the default locale and all user
 * preferences are ignored.
 */
final class SetCortoTranslationsLocaleListener
{
    /**
     * @var LocaleProvider
     */
    private $localeProvider;

    /**
     * @var Zend_Translate_Adapter
     */
    private $translator;

    public function __construct(LocaleProvider $localeProvider)
    {
        $this->localeProvider = $localeProvider;
        $this->translator = EngineBlock_ApplicationSingleton::getInstance()->getTranslator()->getAdapter();
    }

    public function onKernelRequest()
    {
        $locale = $this->localeProvider->getLocale();

        $this->translator->setLocale($locale);
    }
}
