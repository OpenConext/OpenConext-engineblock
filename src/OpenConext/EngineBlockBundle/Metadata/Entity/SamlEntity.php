<?php

namespace OpenConext\EngineBlockBundle\Metadata\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenConext\EngineBlock\Exception\RuntimeException;
use OpenConext\EngineBlock\Metadata\Model\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Model\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Value\SamlEntityUuid;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;

/**
 * @ORM\Entity(repositoryClass="OpenConext\EngineBlock\Metadata\Repository\SamlEntityRepository")
 * @ORM\Table(
 *      name="saml_entity",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="uniq_saml_entity_entity_id_entity_type",
 *              columns={"entity_id", "entity_type"}
 *          )
 *      }
 * )
 */
class SamlEntity
{
    /**
     * @var SamlEntityUuid
     *
     * @ORM\Id
     * @ORM\Column(type="engineblock_saml_entity_uuid")
     */
    private $samlEntityUuid;

    /**
     * @var EntityId
     *
     * @ORM\Column(type="engineblock_entity_id")
     */
    private $entityId;

    /**
     * @var EntityType
     *
     * @ORM\Column(type="engineblock_entity_type")
     */
    private $entityType;

    /**
     * @var array
     *
     * @ORM\Column(type="engineblock_json_metadata")
     */
    private $metadata;

    /**
     * @param Entity $entity
     * @param array  $metadata
     */
    public function __construct(Entity $entity, array $metadata)
    {
        $this->samlEntityUuid = SamlEntityUuid::forEntity($entity);
        $this->entityId       = $entity->getEntityId();
        $this->entityType     = $entity->getEntityType();
        $this->metadata       = $metadata;
    }

    /**
     * @return ServiceProvider
     */
    public function toServiceProvider()
    {
        if (!$this->entityType->isServiceProvider()) {
            throw new RuntimeException(
                'Cannot convert a SamlEntity to Service Provider if not of type EntityType::TYPE_SP'
            );
        }

        return ServiceProvider::create(new Entity($this->entityId, $this->entityType));
    }

    /**
     * @return IdentityProvider
     */
    public function toIdentityProvider()
    {
        if (!$this->entityType->isIdentityProvider()) {
            throw new RuntimeException(
                'Cannot convert a SamlEntity to IdentityProvider if not of type EntityType::TYPE_IDP'
            );
        }

        return IdentityProvider::create(new Entity($this->entityId, $this->entityType));
    }

    /**
     * @return SamlEntityUuid
     */
    public function getSamlEntityUuid()
    {
        return $this->samlEntityUuid;
    }
}
