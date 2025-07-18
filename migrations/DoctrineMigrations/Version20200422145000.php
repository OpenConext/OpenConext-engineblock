<?php

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200422145000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sso_provider_roles_eb5 ADD name_pt VARCHAR(255) NOT NULL AFTER name_en, ADD description_pt VARCHAR(255) NOT NULL AFTER description_en, ADD display_name_pt VARCHAR(255) NOT NULL AFTER display_name_en, ADD organization_pt_name LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\' AFTER organization_en_name, ADD keywords_pt VARCHAR(255) NOT NULL AFTER keywords_en, ADD support_url_pt VARCHAR(255) DEFAULT NULL AFTER support_url_en
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sso_provider_roles_eb5 DROP name_pt, DROP description_pt, DROP display_name_pt, DROP organization_pt_name, DROP keywords_pt, DROP support_url_pt');
    }
}
