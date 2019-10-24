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
use OpenConext\EngineBlock\Metadata\Factory\IdentityProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Factory\ValueObject\EngineBlockConfiguration;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Organization;

class EngineBlockIdentityProviderInformation extends AbstractIdentityProvider
{
    /**
     * @var EngineBlockConfiguration
     */
    private $engineBlockConfiguration;

    public function __construct(IdentityProviderEntityInterface $entity, EngineBlockConfiguration $engineBlockConfiguration)
    {
        parent::__construct($entity);
        $this->engineBlockConfiguration = $engineBlockConfiguration;
    }

    public function getNameNl(): string
    {
        return $this->engineBlockConfiguration->getName();
    }

    public function getNameEn(): string
    {
        return $this->engineBlockConfiguration->getName();
    }

    public function getDisplayNameNl(): string
    {
        return $this->engineBlockConfiguration->getName();
    }

    public function getDisplayNameEn(): string
    {
        return $this->engineBlockConfiguration->getName();
    }

    public function getDescriptionNl(): string
    {
        return $this->engineBlockConfiguration->getDescription();
    }

    public function getDescriptionEn(): string
    {
        return $this->engineBlockConfiguration->getDescription();
    }

    public function getLogo(): ?Logo
    {
        return $this->engineBlockConfiguration->getLogo();
    }

    public function getOrganizationNl(): ?Organization
    {
        return $this->engineBlockConfiguration->getOrganization();
    }

    public function getOrganizationEn(): ?Organization
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
