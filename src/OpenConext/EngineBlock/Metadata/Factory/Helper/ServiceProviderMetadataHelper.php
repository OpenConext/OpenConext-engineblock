<?php declare(strict_types=1);

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

namespace OpenConext\EngineBlock\Metadata\Factory\Helper;

use OpenConext\EngineBlock\Metadata\Factory\Decorator\AbstractServiceProvider;
use OpenConext\EngineBlock\Metadata\Factory\ServiceProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlockBundle\Localization\LanguageSupportProvider;

class ServiceProviderMetadataHelper extends AbstractServiceProvider
{
    /**
     * @var LanguageSupportProvider
     */
    private $languageSupportProvider;

    public function __construct(ServiceProviderEntityInterface $entity, LanguageSupportProvider $languageSupportProvider)
    {
        $this->languageSupportProvider = $languageSupportProvider;

        parent::__construct($entity);
    }

    public function getOrganizationName($locale) : string
    {
        if (!$this->entity->getOrganization($locale)) {
            return '';
        }
        return $this->entity->getOrganization($locale)->name;
    }

    public function getOrganizationDisplayName($locale) : string
    {
        if (!$this->entity->getOrganization($locale)) {
            return '';
        }
        return $this->entity->getOrganization($locale)->displayName;
    }

    public function getOrganizationUrl($locale) : string
    {
        if (!$this->entity->getOrganization($locale)) {
            return '';
        }
        return $this->entity->getOrganization($locale)->url;
    }

    public function getAssertionConsumerService() : IndexedService
    {
        $services = $this->entity->getAssertionConsumerServices();
        return reset($services);
    }

    /**
     * @return string[]
     */
    public function getPublicKeys(): array
    {
        $keys = [];
        foreach ($this->entity->getCertificates() as $certificate) {
            $pem = $certificate->toCertData();
            $keys[$pem] = $pem;
        }
        return $keys;
    }

    public function hasUiInfo(): bool
    {
        $supported = $this->languageSupportProvider->getSupportedLanguages();

        $info = [];
        foreach ($supported as $locale) {
            $info[] = $this->getDisplayName($locale);
            $info[] = $this->getOrganization($locale);
        }

        return !empty(array_filter($info));
    }

    public function hasOrganizationInfo(): bool
    {
        $supported = $this->languageSupportProvider->getSupportedLanguages();

        $info = [];
        foreach ($supported as $locale) {
            $info[] = $this->getOrganization($locale);
        }

        return !empty(array_filter($info));
    }
}
