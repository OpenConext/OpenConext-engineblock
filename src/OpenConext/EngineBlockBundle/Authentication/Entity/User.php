<?php

namespace OpenConext\EngineBlockBundle\Authentication\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonUuid;

/**
 * @ORM\Entity(repositoryClass="OpenConext\EngineBlockBundle\Authentication\Repository\UserRepository")
 */
class User
{
    /**
     * @var
     *
     * @ORM\Id
     * @ORM\Column(length=64, options={"fixed"=true})
     */
    public $collabPersonId;

    /**
     * @var CollabPersonUuid
     *
     * @ORM\Column(name="id", type="engineblock_collab_person_uuid")
     */
    public $collabPersonUuid;
}
