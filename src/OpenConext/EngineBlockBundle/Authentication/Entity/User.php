<?php

/**
 * Copyright 2014 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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
