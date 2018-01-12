<?php

namespace OpenConext\EngineBlock\Metadata\Model;

use OpenConext\EngineBlock\Exception\DomainException;
use OpenConext\EngineBlock\Metadata\Value\IdentityProviderAttributes;
use OpenConext\EngineBlock\Metadata\Value\IdentityProviderConfiguration;
use OpenConext\EngineBlock\Metadata\Value\IdentityProviderSamlConfiguration;
use OpenConext\EngineBlock\Metadata\Value\SamlEntityUuid;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;

final class IdentityProvider
{
    /**
     * @var Entity
     */
    private $entity;

    /**
     * @var IdentityProviderSamlConfiguration
     */
    private $identityProviderSamlConfiguration;

    /**
     * @var IdentityProviderConfiguration
     */
    private $identityProviderConfiguration;

    /**
     * @var IdentityProviderAttributes
     */
    private $identityProviderAttributes;

    public function __construct(
        Entity $entity,
        IdentityProviderSamlConfiguration $identityProviderSamlConfiguration,
        IdentityProviderConfiguration $identityProviderConfiguration,
        IdentityProviderAttributes $identityProviderAttributes
    ) {
        if (!$entity->isIdentityProvider()) {
            $message = sprintf(
                'Can only create an IdentityProvider model for an Entity that is an IdentityProvider, entity "%s"'
                . ' is not an IdentityProvider',
                (string) $entity
            );
            throw new DomainException($message);
        }

        $this->entity = $entity;
        $this->identityProviderSamlConfiguration = $identityProviderSamlConfiguration;
        $this->identityProviderConfiguration = $identityProviderConfiguration;
        $this->identityProviderAttributes = $identityProviderAttributes;
    }

    /**
     * @return EntityId
     */
    public function getEntityId()
    {
        return $this->entity->getEntityId();
    }

    /**
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return IdentityProviderSamlConfiguration
     */
    public function getIdentityProviderSamlConfiguration()
    {
        return $this->identityProviderSamlConfiguration;
    }

    /**
     * @return IdentityProviderConfiguration
     */
    public function getIdentityProviderConfiguration()
    {
        return $this->identityProviderConfiguration;
    }

    /**
     * @return IdentityProviderAttributes
     */
    public function getIdentityProviderAttributes()
    {
        return $this->identityProviderAttributes;
    }

    /**
     * @return SamlEntityUuid
     */
    public function getUuid()
    {
        return SamlEntityUuid::forEntity($this->entity);
    }
}
