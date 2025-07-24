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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Mock;

use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore\AbstractDataStore;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Contracts\Translation\TranslatorInterface;

final class MockTranslator implements TranslatorInterface
{
    /**
     * @var DataCollectorTranslator
     */
    private $translator;
    /**
     * @var AbstractDataStore
     */
    private $dataStore;
    /**
     * @var array
     */
    private $translations;

    public function __construct(DataCollectorTranslator $translator, AbstractDataStore $dataStore)
    {
        $this->translator = $translator;
        $this->dataStore = $dataStore;
        $this->translations = $dataStore->load();
    }

    // Helper methods
    public function setTranslation($key, $value)
    {
        $translations = $this->dataStore->load();
        $translations[$key] = $value;
        $this->dataStore->save($translations);
    }

    public function clear()
    {
        $this->dataStore->save([]);
    }

    // Decorated methods
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        $this->translator->getCatalogue($locale)->add($this->translations);
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        $this->translator->getCatalogue($locale)->add($this->translations);
        return $this->translator->transChoice($id, $number, $parameters, $domain, $locale);
    }

    public function setLocale($locale)
    {
        $this->translator->setLocale($locale);
    }

    public function getLocale()
    {
        return $this->translator->getLocale();
    }
}
