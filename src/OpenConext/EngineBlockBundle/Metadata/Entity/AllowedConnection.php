<?php

namespace OpenConext\EngineBlockBundle\Metadata\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenConext\EngineBlock\Metadata\Value\SamlEntityUuid;
use OpenConext\Value\Saml\EntityId;

/**
 * @ORM\Entity(repositoryClass="OpenConext\EngineBlock\Metadata\Repository\AllowedConnectionRepository")
 */
class AllowedConnection
{
    /**
     * @var SamlEntityUuid
     *
     * @ORM\Id
     * @ORM\Column(type="engineblock_saml_entity_uuid")
     */
    private $serviceProviderUuid;

    /**
     * @var EntityId
     *
     * @ORM\Id
     * @ORM\Column(type="engineblock_saml_entity_uuid")
     */
    private $identityProviderUuid;

    /**
     * @param SamlEntity $serviceProvider
     * @param SamlEntity $identityProvider
     *
     * @return AllowedConnection
     */
    public static function connect(SamlEntity $serviceProvider, SamlEntity $identityProvider)
    {
        return new self($serviceProvider->getSamlEntityUuid(), $identityProvider->getSamlEntityUuid());
    }

    private function __construct(SamlEntityUuid $serviceProvider, SamlEntityUuid $identityProvider)
    {
        $this->serviceProviderUuid  = $serviceProvider;
        $this->identityProviderUuid = $identityProvider;
    }

    /**
     * @return SamlEntityUuid
     */
    public function getServiceProviderUuid()
    {
        return $this->serviceProviderUuid;
    }

    /**
     * @return SamlEntityUuid
     */
    public function getIdentityProviderUuid()
    {
        return $this->identityProviderUuid;
    }
}
