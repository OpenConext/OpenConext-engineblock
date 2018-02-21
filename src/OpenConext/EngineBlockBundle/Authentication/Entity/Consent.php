<?php

namespace OpenConext\EngineBlockBundle\Authentication\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Note: this entity is currently only used to configure doctrine to create
 * the schema on installation. The ConsentService and ConsentRepository do not
 * use entities.
 *
 * @ORM\Entity()
 * @ORM\Table(indexes={
 *     @ORM\Index(name="hashed_user_id", columns={"hashed_user_id"}),
 *     @ORM\Index(name="service_id", columns={"service_id"}),
 * })
 */
class Consent
{
    /**
     * @var DateTime
     *
     * @ORM\Column(name="consent_date", type="datetime", nullable=false)
     */
    public $date;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=80)
     */
    public $hashedUserId;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    public $serviceId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=80)
     */
    public $attribute;

    /**
     * @var string
     *
     * @ORM\Column(name="consent_type", type="string", nullable=true, length=20, options={"default": "explicit"})
     */
    public $type;
}
