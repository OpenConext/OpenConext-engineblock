<?php

/**
 * Copyright 2026 SURFnet B.V.
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

declare(strict_types=1);

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;

final class Version20260602000000 extends AbstractEngineBlockMigration
{
    public function getDescription(): string
    {
        return 'Replace non-unique idx_user_uuid with unique index uq_user_uuid on user.uuid';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ADD UNIQUE INDEX `uq_user_uuid` (`uuid`), DROP INDEX `idx_user_uuid`');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ADD INDEX `idx_user_uuid` (`uuid`), DROP INDEX `uq_user_uuid`');
    }
}
