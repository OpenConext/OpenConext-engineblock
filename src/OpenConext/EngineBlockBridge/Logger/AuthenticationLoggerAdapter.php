<?php

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
     * @param ServiceProvider   $serviceProvider
     * @param IdentityProvider  $identityProvider
     * @param                   $collabPersonId
     * @param                   $keyId
     * @param ServiceProvider[] $proxiedServiceProviders
     */
    public function logLogin(
        ServiceProvider $serviceProvider,
        IdentityProvider $identityProvider,
        $collabPersonId,
        $keyId,
        array $proxiedServiceProviders
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
            $keyId
        );
    }
}
