<?php declare(strict_types=1);

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200226103409 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX idx_sso_provider_roles_publish_in_edugain ON sso_provider_roles_eb5');
        $this->addSql('ALTER TABLE sso_provider_roles_eb5 DROP publish_in_edugain, DROP publish_in_edu_gain_date');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sso_provider_roles_eb5 ADD publish_in_edugain TINYINT(1) NOT NULL, ADD publish_in_edu_gain_date DATE DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_sso_provider_roles_publish_in_edugain ON sso_provider_roles_eb5 (publish_in_edugain)');
    }
}
