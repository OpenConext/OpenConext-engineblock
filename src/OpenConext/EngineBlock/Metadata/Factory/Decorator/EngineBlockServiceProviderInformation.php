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

use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Factory\ServiceProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Factory\ValueObject\EngineBlockConfiguration;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Organization;

/**
 * This decoration is used to add non functional and strictly informational data to an entity
 */
class EngineBlockServiceProviderInformation extends AbstractServiceProvider
{
    /**
     * @var EngineBlockConfiguration
     */
    private $engineBlockConfiguration;

    public function __construct(ServiceProviderEntityInterface $entity, EngineBlockConfiguration $engineBlockConfiguration)
    {
        parent::__construct($entity);
        $this->engineBlockConfiguration = $engineBlockConfiguration;
    }

    public function getName($locale): string
    {
        return $this->engineBlockConfiguration->getName();
    }

    public function getDisplayName(string $locale): string
    {
        return $this->engineBlockConfiguration->getName();
    }

    public function getDescription(string $locale): string
    {
        return $this->engineBlockConfiguration->getDescription();
    }

    public function getLogo(): ?Logo
    {
        return $this->engineBlockConfiguration->getLogo();
    }

    public function getOrganization($locale): ?Organization
    {
        return $this->engineBlockConfiguration->getOrganization();
    }

    /**
     * @return ContactPerson[]
     */
    public function getContactPersons(): array
    {
        return $this->engineBlockConfiguration->getContactPersons();
    }
}
