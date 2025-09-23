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

namespace OpenConext\EngineBlockBundle\Authentication\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenConext\EngineBlockBundle\Authentication\Repository\ServiceProviderUuidRepository;

#[ORM\Entity(repositoryClass: ServiceProviderUuidRepository::class)]
#[ORM\Index(name: 'service_provider_entity_id', columns: ['service_provider_entity_id'], options: ['lengths' => [255]])]
class ServiceProviderUuid
{
    /**
     * @var string
     */
    #[ORM\Id]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 36, options: ['fixed' => true])]
    public ?string $uuid = null;

    /**
     * @var string
     */
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 1024)]
    public ?string $serviceProviderEntityId = null;
}
