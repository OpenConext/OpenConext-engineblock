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
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore\JsonDataStore;
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
     * @var string|null Path template with %s placeholder for locale, e.g. "/tmp/eb-fixtures/translator_mock_%s.json"
     */
    private $localeDataStoreTemplate;

    /**
     * @var JsonDataStore[] Keyed by locale, lazily instantiated
     */
    private $localeDataStores = [];

    public function __construct(
        DataCollectorTranslator $translator,
        AbstractDataStore $dataStore,
        string $localeDataStoreTemplate = null
    ) {
        $this->translator = $translator;
        $this->dataStore = $dataStore;
        $this->localeDataStoreTemplate = $localeDataStoreTemplate;
    }

    // Helper methods
    public function setTranslation(string $key, string $value, string $locale = null): void
    {
        if ($locale !== null) {
            $store = $this->getLocaleDataStore($locale);
            $translations = $store->load();
            $translations[$key] = $value;
            $store->save($translations);
        } else {
            $translations = $this->dataStore->load();
            $translations[$key] = $value;
            $this->dataStore->save($translations);
        }
    }

    public function clear(): void
    {
        $this->dataStore->save([]);
        foreach ($this->localeDataStores as $store) {
            $store->save([]);
        }
        $this->localeDataStores = [];
    }

    // Decorated methods
    public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        $translations = $this->dataStore->load();

        if ($locale !== null) {
            $localeTranslations = $this->getLocaleDataStore($locale)->load();
            $translations = array_merge($translations, $localeTranslations);
        }

        $this->translator->getCatalogue($locale)->add($translations);
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function setLocale(string $locale): void
    {
        $this->translator->setLocale($locale);
    }

    public function getLocale(): string
    {
        return $this->translator->getLocale();
    }

    private function getLocaleDataStore(string $locale): JsonDataStore
    {
        if (!isset($this->localeDataStores[$locale])) {
            $filePath = sprintf($this->localeDataStoreTemplate, $locale);
            $this->localeDataStores[$locale] = new JsonDataStore($filePath);
        }
        return $this->localeDataStores[$locale];
    }
}
