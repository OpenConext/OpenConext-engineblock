<?php

declare(strict_types=1);

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;

/**
 * Change to the consent schema
 * 1. Added the `attribute_stable` column, string(80), nullable
 * 2. Changed the `attribute` column, has been made nullable
 */
final class Version20260315000001 extends AbstractEngineBlockMigration
{
    public function getDescription(): string
    {
        return 'Add attribute_stable column to consent table and make attribute nullable';
    }

    public function preUp(Schema $schema): void
    {
        parent::preUp($schema);
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE consent ADD attribute_stable VARCHAR(80) DEFAULT NULL, CHANGE attribute attribute VARCHAR(80) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE consent SET attribute = attribute_stable WHERE attribute IS NULL AND attribute_stable IS NOT NULL');
        $this->addSql('ALTER TABLE consent CHANGE attribute attribute VARCHAR(80) NOT NULL');
        $this->addSql('ALTER TABLE consent DROP attribute_stable');
    }
}
