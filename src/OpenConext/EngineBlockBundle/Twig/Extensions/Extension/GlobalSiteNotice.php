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

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\TwigFunction;
use Twig_Extension;

class GlobalSiteNotice extends Twig_Extension
{
    /**
     * @var bool
     */
    private $shouldDisplayGlobalSiteNotice;

    /**
     * @var String
     */
    private $allowedHtml;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        bool $shouldDisplayGlobalSiteNotice,
        string $allowedHtml,
        TranslatorInterface $translator
    ) {
        $this->shouldDisplayGlobalSiteNotice = $shouldDisplayGlobalSiteNotice;
        $this->allowedHtml = $allowedHtml;
        $this->translator = $translator;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('shouldDisplayGlobalSiteNotice', [$this, 'shouldDisplayGlobalSiteNotice']),
            new TwigFunction('getGlobalSiteNotice', [$this, 'getGlobalSiteNotice']),
            new TwigFunction('getAllowedHtmlForNotice', [$this, 'getAllowedHtmlForNotice']),
        ];
    }

    public function shouldDisplayGlobalSiteNotice() : bool
    {
        return $this->shouldDisplayGlobalSiteNotice;
    }

    public function getGlobalSiteNotice(): string
    {
        return $this->translator->trans('site_notice');
    }

    public function getAllowedHtmlForNotice(): string
    {
        return $this->allowedHtml;
    }
}
