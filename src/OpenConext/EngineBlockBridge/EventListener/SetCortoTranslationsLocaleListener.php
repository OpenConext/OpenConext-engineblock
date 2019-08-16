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

namespace OpenConext\EngineBlockBridge\EventListener;

use EngineBlock_ApplicationSingleton;
use OpenConext\EngineBlockBundle\Localization\LocaleProvider;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(LocaleProvider $localeProvider)
    {
        $this->localeProvider = $localeProvider;
        $this->translator = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getTranslator();
    }

    public function onKernelRequest()
    {
        $locale = $this->localeProvider->getLocale();

        $this->translator->setLocale($locale);
    }
}
