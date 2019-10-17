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

namespace OpenConext\EngineBlock\Metadata\Factory\Decorator;

use OpenConext\EngineBlock\Metadata\Factory\ServiceProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Service\TimeProvider\TimeProvider;

class EngineBlockServiceProviderMetadata extends AbstractServiceProvider
{
    public function __construct(ServiceProviderEntityInterface $entity)
    {
        parent::__construct($entity);
    }

    public function getOrganization() : string
    {
        return $this->entity->getOrganizationEn()->name;
    }

    public function getOrganizationSupportUrl() : string
    {
        return $this->entity->getOrganizationEn()->url;
    }

    public function getAssertionConsumerService() : IndexedService
    {
        return reset($this->entity->getAssertionConsumerServices());
    }

    public function getPublicKeys(): array
    {
        $keys = [];
        foreach ($this->entity->getCertificates() as $certificate) {
            $pem = $certificate->toCertData();
            $keys[$pem] = $pem;
        }
        return $keys;
    }
}