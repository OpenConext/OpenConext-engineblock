<?php declare(strict_types=1);

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211019150744 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sso_provider_roles_eb6 (id INT AUTO_INCREMENT NOT NULL, entity_id VARCHAR(255) NOT NULL, name_nl VARCHAR(255) DEFAULT NULL, name_en VARCHAR(255) DEFAULT NULL, name_pt VARCHAR(255) DEFAULT NULL, description_nl VARCHAR(255) DEFAULT NULL, description_en VARCHAR(255) DEFAULT NULL, description_pt VARCHAR(255) DEFAULT NULL, display_name_nl VARCHAR(255) DEFAULT NULL, display_name_en VARCHAR(255) DEFAULT NULL, display_name_pt VARCHAR(255) DEFAULT NULL, logo LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:engineblock_logo)\', organization_nl_name LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:engineblock_organization)\', organization_en_name LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:engineblock_organization)\', organization_pt_name LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:engineblock_organization)\', keywords_nl VARCHAR(255) DEFAULT NULL, keywords_en VARCHAR(255) DEFAULT NULL, keywords_pt VARCHAR(255) DEFAULT NULL, certificates LONGTEXT NOT NULL COMMENT \'(DC2Type:engineblock_certificate_array)\', workflow_state VARCHAR(255) NOT NULL, contact_persons LONGTEXT NOT NULL COMMENT \'(DC2Type:engineblock_contact_person_array)\', name_id_format VARCHAR(255) DEFAULT NULL, name_id_formats LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', single_logout_service LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:engineblock_service)\', requests_must_be_signed TINYINT(1) NOT NULL, manipulation LONGTEXT DEFAULT NULL, coins LONGTEXT NOT NULL COMMENT \'(DC2Type:engineblock_metadata_coins)\', type VARCHAR(255) NOT NULL, attribute_release_policy LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:engineblock_attribute_release_policy)\', assertion_consumer_services LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:engineblock_indexed_service_array)\', allowed_idp_entity_ids LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', allow_all TINYINT(1) DEFAULT NULL, requested_attributes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:engineblock_requested_attribute_array)\', support_url_en VARCHAR(255) DEFAULT NULL, support_url_nl VARCHAR(255) DEFAULT NULL, support_url_pt VARCHAR(255) DEFAULT NULL, enabled_in_wayf TINYINT(1) DEFAULT NULL, single_sign_on_services LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:engineblock_service_array)\', consent_settings LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', shib_md_scopes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:engineblock_shib_md_scope_array)\', INDEX idx_sso_provider_roles_eb6_type (type), INDEX idx_sso_provider_roles_eb6_entity_id (entity_id), UNIQUE INDEX idx_sso_provider_roles_eb6_entity_id_type (type, entity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sso_provider_roles_eb6');
    }
}
