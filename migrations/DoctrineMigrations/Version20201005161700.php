<?php declare(strict_types=1);

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201005161700 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        if ($schema->getTable('sso_provider_roles_eb5')->hasIndex('idx_sso_provider_roles_publish_in_edugain')) {
            $this->addSql('DROP INDEX idx_sso_provider_roles_publish_in_edugain ON sso_provider_roles_eb5');
        }
        if ($schema->getTable('sso_provider_roles_eb5')->hasColumn('publish_in_edugain')) {
            $this->addSql('ALTER TABLE sso_provider_roles_eb5 DROP publish_in_edugain');
        }
        if ($schema->getTable('sso_provider_roles_eb5')->hasColumn('publish_in_edu_gain_date')) {
            $this->addSql('ALTER TABLE sso_provider_roles_eb5 DROP publish_in_edu_gain_date');
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sso_provider_roles_eb5 ADD publish_in_edugain TINYINT(1) NOT NULL, ADD publish_in_edu_gain_date DATE DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_sso_provider_roles_publish_in_edugain ON sso_provider_roles_eb5 (publish_in_edugain)');
    }
}
