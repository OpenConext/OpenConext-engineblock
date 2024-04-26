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

namespace OpenConext\EngineBlockBridge\Logger;

use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\KeyId;
use OpenConext\EngineBlock\Logger\AuthenticationLogger;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;

class AuthenticationLoggerAdapter
{
    /**
     * @var AuthenticationLogger
     */
    private $authenticationLogger;

    public function __construct(AuthenticationLogger $authenticationLogger)
    {
        $this->authenticationLogger = $authenticationLogger;
    }

    /**
     * @param ServiceProvider $serviceProvider
     * @param IdentityProvider $identityProvider
     * @param string $collabPersonId
     * @param string|null $keyId
     * @param array $proxiedServiceProviders
     * @param string $originalNameId
     * @param string|null $authnContextClassRef
     * @param string|null $engineSsoEndpointUsed
     * @param array|null $requestedIdPlist
     * @param array $logAttributes
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function logLogin(
        ServiceProvider $serviceProvider,
        IdentityProvider $identityProvider,
        string $collabPersonId,
        ?string $keyId,
        array $proxiedServiceProviders,
        string $originalNameId,
        ?string $authnContextClassRef,
        ?string $engineSsoEndpointUsed,
        ?array $requestedIdPlist,
        array $logAttributes = []
    ) {
        $keyId = $keyId ? new KeyId($keyId) : null;

        $proxiedSpEntities = array_map(
            function (ServiceProvider $serviceProvider) {
                return new Entity(new EntityId($serviceProvider->entityId), EntityType::SP());
            },
            $proxiedServiceProviders
        );

        $this->authenticationLogger->logGrantedLogin(
            new Entity(new EntityId($serviceProvider->entityId), EntityType::SP()),
            new Entity(new EntityId($identityProvider->entityId), EntityType::IdP()),
            new CollabPersonId($collabPersonId),
            $proxiedSpEntities,
            $serviceProvider->workflowState,
            $originalNameId,
            $authnContextClassRef,
            $engineSsoEndpointUsed,
            $requestedIdPlist,
            $keyId,
            $logAttributes
        );
    }
}
