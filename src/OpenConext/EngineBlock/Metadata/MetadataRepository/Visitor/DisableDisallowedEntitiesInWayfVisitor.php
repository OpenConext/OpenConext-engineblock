<?php

/**
 * Copyright 2014 SURFnet B.V.
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

namespace OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor;

use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;

/**
 * Class DisableDisallowedEntitiesInWayfVisitor
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor
 */
class DisableDisallowedEntitiesInWayfVisitor implements VisitorInterface
{
    /**
     * @var array
     */
    private $allowedEntityIds;

    /**
     * @param $allowedEntityIds
     */
    public function __construct(array $allowedEntityIds)
    {
        $this->allowedEntityIds = $allowedEntityIds;
    }

    /**
     * {@inheritdoc}
     */
    public function visitIdentityProvider(IdentityProvider $identityProvider)
    {
        if (in_array($identityProvider->entityId, $this->allowedEntityIds)) {
            return;
        }

        $identityProvider->enabledInWayf = false;
    }

    /**
     * {@inheritdoc}
     */
    public function visitServiceProvider(ServiceProvider $serviceProvider)
    {
    }
}
