<?php

namespace OpenConext\EngineBlockBundle\Authentication\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="OpenConext\EngineBlockBundle\Authentication\Repository\ServiceProviderUuidRepository")
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
