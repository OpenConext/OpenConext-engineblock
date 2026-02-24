<?php

declare(strict_types=1);

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Patch/repair migration - Removes the deleted_at index from the consent table if present.
 *
 * On existing databases where the index is already absent this migration is marked as done
 * without executing any SQL. On databases where the index still exists it will be dropped.
 */
final class Version20260224000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Patch migration: Removes the deleted_at index from the consent table. Skips if the index does not exist.';
    }

    public function preUp(Schema $schema): void
    {
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
