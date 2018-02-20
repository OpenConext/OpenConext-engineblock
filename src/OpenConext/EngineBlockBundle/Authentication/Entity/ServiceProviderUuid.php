<?php

namespace OpenConext\EngineBlockBundle\Authentication\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Note: this entity is currently only used to configure doctrine to create
 * the schema on installation.
 *
 * @ORM\Entity()
 * @ORM\Table(indexes={
 *     @ORM\Index(name="service_provider_entity_id", columns={"service_provider_entity_id"}),
 * })
 */
class ServiceProviderUuid
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=36, options={"fixed": true})
     */
    public $uuid;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=1024)
     */
    public $serviceProviderEntityId;
}
