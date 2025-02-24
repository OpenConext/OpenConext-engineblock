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

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Symfony\Contracts\Translation\TranslatorInterface;

class I18n extends AbstractExtension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('trans', [$this, 'translateSingular']),
            new TwigFilter('transchoice', [$this, 'translatePlural']),
        ];
    }

    public function translateSingular($id, array $parameters = [], $domain = null, $locale = null)
    {
        $parameters = $this->addDefaultPlaceholders($parameters);
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function translatePlural($id, $count, array $parameters = [], $domain = null, $locale = null)
    {
        $parameters = $this->addDefaultPlaceholders($parameters);
        $parameters['%count%'] = $count;
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    private function addDefaultPlaceholders(array $parameters)
    {
        $parameters['%suiteName%'] = $this->translator->trans('suite_name');
        $parameters['%supportUrl%'] = $this->translator->trans('openconext_support_url');
        $parameters['%organisationNoun%'] = $this->translator->trans('organisation_noun');
        $parameters['%organisationNounPlural%'] = $this->translator->trans('organisation_noun_plural');
        $parameters['%accountNoun%'] = $this->translator->trans('account_noun');

        return $parameters;
    }
}
