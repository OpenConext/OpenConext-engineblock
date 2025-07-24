<?php declare(strict_types=1);

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230824090020 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('
            ALTER TABLE sso_provider_roles_eb5
            CHANGE organization_nl_name organization_nl_name TEXT DEFAULT NULL COMMENT \'(DC2Type:object)\',
            CHANGE organization_en_name organization_en_name TEXT DEFAULT NULL COMMENT \'(DC2Type:object)\',
            CHANGE organization_pt_name organization_pt_name TEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', 
            CHANGE name_id_format name_id_format VARCHAR(255) DEFAULT NULL, 
            CHANGE single_logout_service single_logout_service TEXT DEFAULT NULL COMMENT \'(DC2Type:object)\',
            CHANGE attribute_release_policy attribute_release_policy TEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', 
            CHANGE assertion_consumer_services assertion_consumer_services TEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', 
            CHANGE allowed_idp_entity_ids allowed_idp_entity_ids MEDIUMTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', 
            CHANGE requested_attributes requested_attributes TEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', 
            CHANGE support_url_en support_url_en VARCHAR(255) DEFAULT NULL, 
            CHANGE support_url_nl support_url_nl VARCHAR(255) DEFAULT NULL, 
            CHANGE support_url_pt support_url_pt VARCHAR(255) DEFAULT NULL, 
            CHANGE single_sign_on_services single_sign_on_services TEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', 
            CHANGE consent_settings consent_settings MEDIUMTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', 
            CHANGE shib_md_scopes shib_md_scopes TEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', 
            CHANGE mdui mdui TEXT NOT NULL COMMENT \'(DC2Type:array)\', 
            CHANGE coins coins TEXT NOT NULL COMMENT \'(DC2Type:array)\', 
            CHANGE name_id_formats name_id_formats TEXT NOT NULL COMMENT \'(DC2Type:array)\', 
            CHANGE contact_persons contact_persons TEXT NOT NULL COMMENT \'(DC2Type:array)\', 
            CHANGE certificates certificates TEXT NOT NULL COMMENT \'(DC2Type:array)\', 
            CHANGE manipulation manipulation TEXT NOT NULL COMMENT \'(DC2Type:array)\'
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('
            ALTER TABLE sso_provider_roles_eb5  
            CHANGE organization_nl_name organization_nl_name LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', 
            CHANGE organization_en_name organization_en_name LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\',
            CHANGE organization_pt_name organization_pt_name LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\',
            CHANGE name_id_format name_id_format VARCHAR(255) DEFAULT NULL,
            CHANGE single_logout_service single_logout_service LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\',
            CHANGE attribute_release_policy attribute_release_policy LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\',
            CHANGE assertion_consumer_services assertion_consumer_services LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\',
            CHANGE allowed_idp_entity_ids allowed_idp_entity_ids LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\',
            CHANGE requested_attributes requested_attributes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\',
            CHANGE support_url_en support_url_en VARCHAR(255) DEFAULT NULL,
            CHANGE support_url_nl support_url_nl VARCHAR(255) DEFAULT NULL,
            CHANGE support_url_pt support_url_pt VARCHAR(255) DEFAULT NULL, 
            CHANGE single_sign_on_services single_sign_on_services LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', 
            CHANGE consent_settings consent_settings LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', 
            CHANGE shib_md_scopes shib_md_scopes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\',
            CHANGE mdui mdui LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\',
            CHANGE coins coins LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\',
            CHANGE name_id_formats name_id_formats LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\',
            CHANGE contact_persons contact_persons LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\',
            CHANGE certificates certificates LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\',
            CHANGE manipulation manipulation LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'
        ');
    }
}
