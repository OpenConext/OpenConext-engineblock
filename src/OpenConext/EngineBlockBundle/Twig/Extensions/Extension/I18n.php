<?php

/**
 * Copyright 2018 SURFnet B.V.
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
use Zend_Translate;

class I18n extends Twig_Extensions_Extension_I18n implements \Twig_Extension_InitRuntimeInterface
{

    /**
     * @var Zend_Translate
     */
    private $translator;

    public function __construct(Zend_Translate $translator)
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
            new \Twig_SimpleFilter('trans', array($this, 'translateSingular')),
        );
    }

    /**
     * @return string
     */
    public function translateSingular()
    {
        $arguments = func_get_args();
        $arguments[0] = $this->translator->translate($arguments[0]);

        if (count($arguments) === 1) {
            return $arguments[0];
        }

        return call_user_func_array('sprintf', $arguments);
    }
}
