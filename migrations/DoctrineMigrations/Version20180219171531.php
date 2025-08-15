<?php

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180219171531 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE db_changelog');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE db_changelog (patch_number INT NOT NULL, branch VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, completed INT DEFAULT NULL, filename VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, hash VARCHAR(32) NOT NULL COLLATE utf8_unicode_ci, description VARCHAR(200) DEFAULT NULL COLLATE utf8_unicode_ci, PRIMARY KEY(patch_number, branch)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }
}
