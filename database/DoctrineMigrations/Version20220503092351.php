<?php declare(strict_types=1);

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Change to the consent schema
 * 1. Added the `attribute_stable` column, string(80), not null
 * 2. Changed the `attribute` column, has been made nullable
 */
final class Version20220503092351 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE consent ADD attribute_stable VARCHAR(80) NOT NULL, CHANGE attribute attribute VARCHAR(80) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE consent DROP attribute_stable, CHANGE attribute attribute VARCHAR(80) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
    }
}
