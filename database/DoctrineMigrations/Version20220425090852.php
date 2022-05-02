<?php declare(strict_types=1);

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220425090852 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE consent DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE consent ADD deleted_at DATETIME NOT NULL');
        $this->addSql('CREATE INDEX deleted_at ON consent (deleted_at)');
        $this->addSql('ALTER TABLE consent ADD PRIMARY KEY (hashed_user_id, service_id, deleted_at)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX deleted_at ON consent');
        $this->addSql('ALTER TABLE consent DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE consent DROP deleted_at');
        $this->addSql('ALTER TABLE consent ADD PRIMARY KEY (hashed_user_id, service_id)');
    }
}
