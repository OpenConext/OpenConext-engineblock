<?php

namespace OpenConext\EngineBlock\Metadata\Model;

use OpenConext\EngineBlock\Metadata\Value\SamlEntityUuid;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;

final class ServiceProvider
{
    /**
     * @var Entity
     */
    private $entity;

    public static function create(Entity $entity)
    {
        return new self($entity);
    }

    private function __construct(Entity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return EntityId
     */
    public function getEntityId()
    {
        return $this->entity->getEntityId();
    }

    /**
     * @return SamlEntityUuid
     */
    public function getUuid()
    {
        return SamlEntityUuid::forEntity($this->entity);
    }
}
