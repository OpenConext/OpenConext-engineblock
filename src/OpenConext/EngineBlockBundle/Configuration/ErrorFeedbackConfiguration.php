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

namespace OpenConext\EngineBlockBundle\Configuration;

use Symfony\Contracts\Translation\TranslatorInterface;

class ErrorFeedbackConfiguration implements ErrorFeedbackConfigurationInterface
{
    private $wikiLinksTranslationPrefix = 'error_feedback_wiki_links_';
    private $idpContactTranslationPrefix = 'error_feedback_idp_contact_label_small_';

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param string $route
     * @return bool
     */
    public function hasWikiLink($route)
    {
        $key = $this->getWikiLinksTranslationKey($route);
        return $this->hasTranslation($key);
    }

    /**
     * @param string $route
     * @return string
     */
    public function getWikiLink($route)
    {
        $key = $this->getWikiLinksTranslationKey($route);
        return $this->translator->trans($key);
    }

    /**
     * @param string $route
     * @return string
     */
    public function getIdpContactShortLabel($route)
    {
        $key = $this->getIdpContactTranslationKey($route);
        return $this->translator->trans($key);
    }

    /**
     * @param string $route
     * @return bool
     */
    public function isIdPContactPage($route)
    {
        $key = $this->getIdpContactTranslationKey($route);
        return $this->hasTranslation($key);
    }

    /**
     * @param $key
     * @return bool
     */
    private function hasTranslation($key)
    {
        $translation = $this->translator->trans($key);
        return $translation != '' && $translation != $key;
    }

    /**
     * @param string $route
     * @return string
     */
    private function getWikiLinksTranslationKey($route)
    {
        return $this->wikiLinksTranslationPrefix.$route;
    }

    /**
     * @param string $route
     * @return string
     */
    private function getIdpContactTranslationKey($route)
    {
        return $this->idpContactTranslationPrefix.$route;
    }
}
