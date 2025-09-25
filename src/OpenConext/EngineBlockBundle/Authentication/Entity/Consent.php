<?php

/**
 * Copyright 2010 SURFnet B.V.
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

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Note: this entity is currently only used to configure doctrine to create
 * the schema on installation. The ConsentService and ConsentRepository do not
 * use entities.
 */
#[ORM\Entity]
#[ORM\Index(name: 'hashed_user_id', columns: ['hashed_user_id'])]
#[ORM\Index(name: 'service_id', columns: ['service_id'])]
#[ORM\Index(name: 'deleted_at', columns: ['deleted_at'])]
class Consent
{
    /**
     * @var DateTime
     */
    #[ORM\Column(name: 'consent_date', type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: false)]
    public \DateTimeInterface $date;

    /**
     * @var string
     */
    #[ORM\Id]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 80)]
    public ?string $hashedUserId = null;

    /**
     * @var string
     */
    #[ORM\Id]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING)]
    public ?string $serviceId = null;

    /**
     * @var string
     */
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 80)]
    public ?string $attribute = null;

    /**
     * @var string
     */
    #[ORM\Column(name: 'consent_type', type: \Doctrine\DBAL\Types\Types::STRING, nullable: true, length: 20, options: ['default' => 'explicit'])]
    public ?string $type = null;

    /**
     * @var DateTime
     */
    #[ORM\Id]
    #[ORM\Column(name: 'deleted_at', type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true, options: ['default' => null])]
    public ?\DateTimeInterface $deletedAt = null;
}
