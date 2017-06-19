<?php

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161123131704 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        // Remove the virtual organisation tables as the functionality has been removed
        $this->addSql('DROP TABLE IF EXISTS virtual_organisation');
        $this->addSql('DROP TABLE IF EXISTS virtual_organisation_group');
        $this->addSql('DROP TABLE virtual_organisation_idp');

        // Remove the implicit_vo_id from the sso_provider_roles
        $this->addSql('ALTER TABLE sso_provider_roles DROP COLUMN implicit_vo_id');
        $this->addSql('ALTER TABLE sso_provider_roles_eb5 DROP COLUMN implicit_vo_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE IF NOT EXISTS virtual_organisation (vo_id VARCHAR(255) NOT NULL, vo_type enum(\'GROUP\',\'STEM\',\'IDP\', \'MIXED\') NOT NULL, PRIMARY KEY (vo_id)) ENGINE=InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS virtual_organisation_group (vo_id VARCHAR(255) NOT NULL, group_id VARCHAR(255) NOT NULL, PRIMARY KEY (vo_id, group_id)) ENGINE=InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS virtual_organisation_idp (vo_id varchar(255) NOT NULL, idp_id varchar(255) NOT NULL, PRIMARY KEY (vo_id, idp_id)) ENGINE=InnoDB');

        $this->addSql('ALTER TABLE sso_provider_roles ADD implicit_vo_id VARCHAR(255) DEFAULT NULL AFTER is_trusted_proxy');
        $this->addSql('ALTER TABLE sso_provider_roles_eb5 ADD implicit_vo_id VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL AFTER is_trusted_proxy');
    }
}
