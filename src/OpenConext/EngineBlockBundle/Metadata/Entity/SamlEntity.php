<?php

namespace OpenConext\EngineBlockBundle\Metadata\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenConext\EngineBlock\Exception\RuntimeException;
use OpenConext\EngineBlock\Metadata\Model\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Model\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Value\IdentityProviderAttributes;
use OpenConext\EngineBlock\Metadata\Value\IdentityProviderConfiguration;
use OpenConext\EngineBlock\Metadata\Value\IdentityProviderSamlConfiguration;
use OpenConext\EngineBlock\Metadata\Value\SamlEntityUuid;
use OpenConext\EngineBlock\Metadata\Value\ServiceProviderAttributes;
use OpenConext\EngineBlock\Metadata\Value\ServiceProviderConfiguration;
use OpenConext\EngineBlock\Metadata\Value\ServiceProviderSamlConfiguration;
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
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) due to the IdP/SP Mapping back and forth
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

    public static function fromServiceProvider(ServiceProvider $serviceProvider)
    {
        return new self(
            SamlEntityUuid::forEntity($serviceProvider->getEntity()),
            $serviceProvider->getEntity()->getEntityId(),
            $serviceProvider->getEntity()->getEntityType(),
            [
                'saml_configuration' => $serviceProvider->getServiceProviderSamlConfiguration()->serialize(),
                'configuration'      => $serviceProvider->getServiceProviderConfiguration()->serialize(),
                'attributes'         => $serviceProvider->getServiceProviderAttributes()->serialize()
            ]
        );
    }

    public static function fromIdentityProvider(IdentityProvider $identityProvider)
    {
        return new self(
            SamlEntityUuid::forEntity($identityProvider->getEntity()),
            $identityProvider->getEntity()->getEntityId(),
            $identityProvider->getEntity()->getEntityType(),
            [
                'saml_configuration' => $identityProvider->getIdentityProviderSamlConfiguration()->serialize(),
                'configuration'      => $identityProvider->getIdentityProviderConfiguration()->serialize(),
                'attributes'         => $identityProvider->getIdentityProviderAttributes()->serialize()
            ]
        );
    }

    /**
     * @param SamlEntityUuid $samlEntityUuid
     * @param EntityId       $entityId
     * @param EntityType     $entityType
     * @param array          $metadata
     */
    private function __construct(
        SamlEntityUuid $samlEntityUuid,
        EntityId $entityId,
        EntityType $entityType,
        array $metadata
    ) {
        $this->samlEntityUuid = $samlEntityUuid;
        $this->entityId       = $entityId;
        $this->entityType     = $entityType;
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

        return new ServiceProvider(
            new Entity($this->entityId, $this->entityType),
            ServiceProviderSamlConfiguration::deserialize($this->metadata['saml_configuration']),
            ServiceProviderConfiguration::deserialize($this->metadata['configuration']),
            ServiceProviderAttributes::deserialize($this->metadata['attributes'])
        );
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

        return new IdentityProvider(
            new Entity($this->entityId, $this->entityType),
            IdentityProviderSamlConfiguration::deserialize($this->metadata['saml_configuration']),
            IdentityProviderConfiguration::deserialize($this->metadata['configuration']),
            IdentityProviderAttributes::deserialize($this->metadata['attributes'])
        );
    }

    /**
     * @return SamlEntityUuid
     */
    public function getSamlEntityUuid()
    {
        return $this->samlEntityUuid;
    }
}
