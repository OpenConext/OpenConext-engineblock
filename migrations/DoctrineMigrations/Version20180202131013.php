<?php

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180202131013 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE allowed_connection');
        $this->addSql('DROP TABLE saml_entity');
        $this->addSql('DROP TABLE sso_provider_roles');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE allowed_connection (service_provider_uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:engineblock_saml_entity_uuid)\', identity_provider_uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:engineblock_saml_entity_uuid)\', PRIMARY KEY(service_provider_uuid, identity_provider_uuid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE saml_entity (saml_entity_uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:engineblock_saml_entity_uuid)\', entity_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:engineblock_entity_id)\', entity_type VARCHAR(20) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:engineblock_entity_type)\', metadata LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:engineblock_json_metadata)\', UNIQUE INDEX uniq_saml_entity_entity_id_entity_type (entity_id, entity_type), PRIMARY KEY(saml_entity_uuid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sso_provider_roles (id INT AUTO_INCREMENT NOT NULL, entity_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, name_nl VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, name_en VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, description_nl VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, description_en VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, display_name_nl VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, display_name_en VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, logo LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', organization_nl_name LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', organization_en_name LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', keywords_nl VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, keywords_en VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, publish_in_edugain TINYINT(1) NOT NULL, certificates LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', workflow_state VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, contact_persons LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', name_id_format VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, name_id_formats LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', single_logout_service LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', publish_in_edu_gain_date DATE DEFAULT NULL, disable_scoping TINYINT(1) NOT NULL, additional_logging TINYINT(1) NOT NULL, requests_must_be_signed TINYINT(1) NOT NULL, response_processing_service_binding VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, manipulation LONGTEXT NOT NULL COLLATE utf8_unicode_ci, type VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, attribute_release_policy LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', assertion_consumer_services LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', is_transparent_issuer TINYINT(1) DEFAULT NULL, is_trusted_proxy TINYINT(1) DEFAULT NULL, display_unconnected_idps_wayf TINYINT(1) DEFAULT NULL, is_consent_required TINYINT(1) DEFAULT NULL, terms_of_service_url VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, skip_denormalization TINYINT(1) DEFAULT NULL, allowed_idp_entity_ids LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', requested_attributes LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', enabled_in_wayf TINYINT(1) DEFAULT NULL, single_sign_on_services LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', guest_qualifier VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, schac_home_organization VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, sps_entity_ids_without_consent LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', hidden TINYINT(1) DEFAULT NULL, shib_md_scopes LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', policy_enforcement_decision_required TINYINT(1) DEFAULT NULL, support_url_en VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, support_url_nl VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, attribute_aggregation_required TINYINT(1) DEFAULT NULL, UNIQUE INDEX idx_sso_provider_roles_entity_id_type (type, entity_id), INDEX idx_sso_provider_roles_type (type), INDEX idx_sso_provider_roles_entity_id (entity_id), INDEX idx_sso_provider_roles_publish_in_edugain (publish_in_edugain), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }
}
