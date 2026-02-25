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

/**
 * Patch/repair migration - Removes the deleted_at index from the consent table if present.
 *
 * On existing databases where the index is already absent this migration is marked as done
 * without executing any SQL. On databases where the index still exists it will be dropped.
 */
final class Version20260224000000 extends AbstractEngineBlockMigration
{
    public function getDescription(): string
    {
        return 'Patch migration: Removes the deleted_at index from the consent table. Skips if the index does not exist.';
    }

    public function preUp(Schema $schema): void
    {
        parent::preUp($schema);

        $indexes = $this->connection->createSchemaManager()->listTableIndexes('consent');
        $this->skipIf(
            !isset($indexes['deleted_at']),
            'Index deleted_at on consent table does not exist. Skipping.'
        );
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `consent` DROP INDEX `deleted_at`');
    }

}
