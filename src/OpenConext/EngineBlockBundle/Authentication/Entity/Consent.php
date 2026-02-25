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
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Note: this entity is currently only used to configure doctrine to create
 * the schema on installation. The ConsentService and ConsentRepository do not
 * use entities.
 */
#[ORM\Entity]
#[ORM\Index(name: 'hashed_user_id', columns: ['hashed_user_id'])]
#[ORM\Index(name: 'service_id', columns: ['service_id'])]
class Consent
{
    /**
     * @var DateTime
     */
    #[ORM\Column(name: 'consent_date', type: Types::DATETIME_MUTABLE, nullable: false)]
    public \DateTimeInterface $date;

    /**
     * @var string
     */
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 80)]
    public ?string $hashedUserId = null;

    /**
     * @var string
     */
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING)]
    public ?string $serviceId = null;

    /**
     * @var string
     */
    #[ORM\Column(type: Types::STRING, length: 80)]
    public ?string $attribute = null;

    /**
     * @var string
     */
    #[ORM\Column(name: 'consent_type', type: Types::STRING, nullable: true, length: 20, options: ['default' => 'explicit'])]
    public ?string $type = null;

    /**
     * @var DateTime
     *
     * Soft-delete sentinel using MariaDB's special zero-date ('0000-00-00 00:00:00') as the "not deleted" value.
     *
     * Active (non-deleted) records have deleted_at = '0000-00-00 00:00:00'.
     * Soft-deleted records have deleted_at = NOW()
     *
     * Queries use `deleted_at IS NULL` to select active records. This works because MariaDB defines that
     * expressions involving a zero-date evaluate to NULL at the database level (see MariaDB DATETIME docs).
     *
     * IMPORTANT deleted_at cannot be made nullable because it is part of the composite primary key (hashed_user_id,
     * service_id, deleted_at). Primary key columns cannot be nullable in MySQL/MariaDB.
     *
     */
    #[ORM\Id]
    #[ORM\Column(name: 'deleted_at', type: Types::DATETIME_MUTABLE, nullable: false, options: ['default' => '0000-00-00 00:00:00'])]
    public ?\DateTimeInterface $deletedAt = null;
}
