<?php

namespace OpenConext\EngineBlockBundle\Authentication\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonUuid;

/**
 * @ORM\Entity(repositoryClass="OpenConext\EngineBlockBundle\Authentication\Repository\UserRepository")
 * @ORM\Table(indexes={@ORM\Index(name="idx_user_uuid", columns={"uuid"})})
 */
class User
{
    /**
     * @var
     *
     * @ORM\Id
     * @ORM\Column(type="engineblock_collab_person_id")
     */
    public $collabPersonId;

    /**
     * @var CollabPersonUuid
     *
     *
     * @ORM\Column(name="uuid", type="engineblock_collab_person_uuid")
     */
    public $collabPersonUuid;
}
