<?php

namespace OpenConext\EngineBlockBridge\Logger;

use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;
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
     * @param ServiceProvider  $serviceProvider
     * @param IdentityProvider $identityProvider
     * @param                  $collabPersonId
     * @param                  $keyId
     */
    public function logLogin(
        ServiceProvider $serviceProvider,
        IdentityProvider $identityProvider,
        $collabPersonId,
        $keyId
    ) {
        $keyId = $keyId ? new KeyId($keyId) : null;

        $this->authenticationLogger->logGrantedLogin(
            new Entity(new EntityId($serviceProvider->entityId), EntityType::SP()),
            new Entity(new EntityId($identityProvider->entityId), EntityType::IdP()),
            new CollabPersonId($collabPersonId),
            $keyId
        );
    }
}
