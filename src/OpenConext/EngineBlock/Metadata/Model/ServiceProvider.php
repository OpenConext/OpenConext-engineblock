<?php

namespace OpenConext\EngineBlock\Metadata\Model;

use OpenConext\EngineBlock\Metadata\Value\SamlEntityUuid;
use OpenConext\EngineBlock\Metadata\Value\ServiceProviderAttributes;
use OpenConext\EngineBlock\Metadata\Value\ServiceProviderConfiguration;
use OpenConext\EngineBlock\Metadata\Value\ServiceProviderSamlConfiguration;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;

final class ServiceProvider
{
    /**
     * @var Entity
     */
    private $entity;

    /**
     * @var ServiceProviderSamlConfiguration
     */
    private $serviceProviderSamlConfiguration;

    /**
     * @var ServiceProviderConfiguration
     */
    private $serviceProviderConfiguration;

    /**
     * @var ServiceProviderAttributes
     */
    private $serviceProviderAttributes;

    public function __construct(
        Entity $entity,
        ServiceProviderSamlConfiguration $serviceProviderSamlConfiguration,
        ServiceProviderConfiguration $serviceProviderConfiguration,
        ServiceProviderAttributes $serviceProviderAttributes
    ) {
        $this->entity = $entity;
        $this->serviceProviderSamlConfiguration = $serviceProviderSamlConfiguration;
        $this->serviceProviderConfiguration = $serviceProviderConfiguration;
        $this->serviceProviderAttributes = $serviceProviderAttributes;
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
     * @return ServiceProviderSamlConfiguration
     */
    public function getServiceProviderSamlConfiguration()
    {
        return $this->serviceProviderSamlConfiguration;
    }

    /**
     * @return ServiceProviderConfiguration
     */
    public function getServiceProviderConfiguration()
    {
        return $this->serviceProviderConfiguration;
    }

    /**
     * @return ServiceProviderAttributes
     */
    public function getServiceProviderAttributes()
    {
        return $this->serviceProviderAttributes;
    }

    /**
     * @return SamlEntityUuid
     */
    public function getUuid()
    {
        return SamlEntityUuid::forEntity($this->entity);
    }
}
