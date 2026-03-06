<?php

declare(strict_types=1);

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Change to the consent schema
 * 1. Added the `attribute_stable` column, string(80), nullable
 * 2. Changed the `attribute` column, has been made nullable
 */
final class Version20220503092351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add attribute_stable column to consent table and make attribute nullable';
    }

    public function preUp(Schema $schema): void
    {
        $tables = $this->connection->createSchemaManager()->listTableNames();
        $tableExists = in_array('consent', $tables, true);

        if (!$tableExists) {
            $this->skipIf(true, 'Table consent does not exist yet (fresh install, baseline will create it). Skipping.');
            return;
        }

        $columnExists = (bool) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'consent'
               AND COLUMN_NAME = 'attribute_stable'"
        );
        $this->skipIf(
            $columnExists,
            'Column attribute_stable already exists (fresh install via baseline). Skipping.'
        );
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE consent ADD attribute_stable VARCHAR(80) DEFAULT NULL, CHANGE attribute attribute VARCHAR(80) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE consent DROP attribute_stable, CHANGE attribute attribute VARCHAR(80) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
    }
}
