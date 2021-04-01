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

use Twig_Extensions_Extension_I18n;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_SimpleFilter;

class I18n extends Twig_Extensions_Extension_I18n
{

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('trans', array($this, 'translateSingular')),
            new Twig_SimpleFilter('transchoice', array($this, 'translatePlural')),
        );
    }

    /**
     * @return string
     */
    public function translateSingular()
    {
        $args = func_get_args();
        return call_user_func_array(
            [$this->translator, 'trans'],
            $this->prepareDefaultPlaceholders($args)
        );
    }

    /**
     * @return string
     */
    public function translatePlural()
    {
        $args = func_get_args();
        return call_user_func_array(
            [$this->translator, 'transChoice'],
            $this->prepareDefaultPlaceholders($args)
        );
    }

    /**
     * @param array $args
     * @return array
     */
    private function prepareDefaultPlaceholders(array $args)
    {
        $args[1]['%suiteName%'] = $this->translator->trans('suite_name');
        $args[1]['%supportUrl%'] = $this->translator->trans('openconext_support_url');
        $args[1]['%organisationNoun%'] = $this->translator->trans('organisation_noun');
        $args[1]['%organisationNounPlural%'] = $this->translator->trans('organisation_noun_plural');
        $args[1]['%accountNoun%'] = $this->translator->trans('account_noun');

        return $args;
    }
}
