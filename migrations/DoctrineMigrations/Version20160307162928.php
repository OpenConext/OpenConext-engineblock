<?php

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160307162928 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE saml_entity (saml_entity_uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:engineblock_saml_entity_uuid)\', entity_id VARCHAR(255) NOT NULL COMMENT \'(DC2Type:engineblock_entity_id)\', entity_type VARCHAR(20) NOT NULL COMMENT \'(DC2Type:engineblock_entity_type)\', metadata LONGTEXT NOT NULL COMMENT \'(DC2Type:engineblock_json_metadata)\', UNIQUE INDEX uniq_saml_entity_entity_id_entity_type (entity_id, entity_type), PRIMARY KEY(saml_entity_uuid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE allowed_connection (service_provider_uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:engineblock_saml_entity_uuid)\', identity_provider_uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:engineblock_saml_entity_uuid)\', PRIMARY KEY(service_provider_uuid, identity_provider_uuid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE saml_entity');
        $this->addSql('DROP TABLE allowed_connection');
    }
}
