<?php

namespace OpenConext\EngineBlockBundle\Authentication\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Note: this entity is currently only used to configure doctrine to create
 * the schema on installation.
 *
 * @ORM\Entity()
 * @ORM\Table(indexes={
 *     @ORM\Index(name="user_uuid", columns={"user_uuid", "service_provider_uuid"}),
 * })
 */
class SamlPersistentId
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=40, options={"fixed": true, "comment": "SHA1 of service_provider_uuid + user_uuid"})
     */
    public $persistentId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=36, options={"fixed": true})
     */
    public $userUuid;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=36, options={"fixed": true})
     */
    public $serviceProviderUuid;
}
