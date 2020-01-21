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

use OpenConext\EngineBlock\Metadata\Factory\Decorator\AbstractIdentityProvider;
use OpenConext\EngineBlock\Metadata\Service;

class IdentityProviderMetadataHelper extends AbstractIdentityProvider
{
    public function getOrganizationNameEn() : string
    {
        if (!$this->entity->getOrganizationEn()) {
            return '';
        }
        return $this->entity->getOrganizationEn()->name;
    }

    public function getOrganizationNameNl() : string
    {
        if (!$this->entity->getOrganizationNl()) {
            return '';
        }
        return $this->entity->getOrganizationNl()->name;
    }

    public function getOrganizationUrlEn() : string
    {
        if (!$this->entity->getOrganizationEn()) {
            return '';
        }
        return $this->entity->getOrganizationEn()->url;
    }

    public function getOrganizationUrlNl() : string
    {
        if (!$this->entity->getOrganizationNl()) {
            return '';
        }
        return $this->entity->getOrganizationNl()->url;
    }

    public function getSsoLocation() : string
    {
        /** @var Service $service */
        $service = reset($this->entity->getSingleSignOnServices());
        return $service->location;
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

    public function hasOrganizationInfo(): bool
    {
        $info = [
            $this->entity->getOrganizationEn(),
            $this->entity->getOrganizationNl(),
        ];

        return !empty(array_filter($info));
    }
}
