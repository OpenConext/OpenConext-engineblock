<?php
declare(strict_types=1);

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

namespace OpenConext\EngineBlockBundle\Metadata\ValueObjects;

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;

class ServiceProviderMetadata
{
    /**
     * @var ServiceProvider
     */
    private $entity;

    public function __construct(ServiceProvider $entity)
    {
        $this->entity = $entity;
    }

    public function getEntityId(): string
    {
        return $this->entity->entityId;
    }

    public function getAcsLocation(): string
    {
        return $this->entity->assertionConsumerServices[0]->location;
    }

    public function getPublicKeys(): array
    {
        $keys = [];
        foreach ($this->entity->certificates as $certificate) {
            $pem = $certificate->toCertData();
            $keys[$pem] = $pem;
        }
        return $keys;
    }
}
