<?php

declare(strict_types=1);

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260817133323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create sso_provider_roles_eb6 table that no longer contains serialized objects';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE sso_provider_roles_eb6 (
                id                          INT AUTO_INCREMENT NOT NULL,
                entity_id                   VARCHAR(255) NOT NULL,
                name_nl                     VARCHAR(255),
                name_en                     VARCHAR(255),
                name_pt                     VARCHAR(255),
                description_nl              VARCHAR(255),
                description_en              VARCHAR(255),
                description_pt              VARCHAR(255),
                display_name_nl             VARCHAR(255),
                display_name_en             VARCHAR(255),
                display_name_pt             VARCHAR(255),
                logo                        JSON,
                organization_nl_name        JSON DEFAULT NULL,
                organization_en_name        JSON DEFAULT NULL,
                organization_pt_name        JSON DEFAULT NULL,
                keywords_nl                 VARCHAR(255),
                keywords_en                 VARCHAR(255),
                keywords_pt                 VARCHAR(255),
                certificates                JSON,
                workflow_state              VARCHAR(255) NOT NULL,
                contact_persons             JSON,
                name_id_format              VARCHAR(255) DEFAULT NULL,
                name_id_formats             JSON NOT NULL,
                single_logout_service       JSON DEFAULT NULL,
                requests_must_be_signed     TINYINT NOT NULL,
                manipulation                TEXT,
                coins                       LONGTEXT NOT NULL,
                mdui                        LONGTEXT NOT NULL,
                type                        VARCHAR(255) NOT NULL,
                attribute_release_policy    JSON DEFAULT NULL,
                assertion_consumer_services JSON DEFAULT NULL,
                allowed_idp_entity_ids      JSON DEFAULT NULL,
                allow_all                   TINYINT DEFAULT NULL,
                requested_attributes        JSON DEFAULT NULL,
                support_url_en              VARCHAR(255) DEFAULT NULL,
                support_url_nl              VARCHAR(255) DEFAULT NULL,
                support_url_pt              VARCHAR(255) DEFAULT NULL,
                enabled_in_wayf             TINYINT DEFAULT NULL,
                single_sign_on_services     JSON DEFAULT NULL,
                consent_settings            LONGTEXT DEFAULT NULL,
                shib_md_scopes              JSON DEFAULT NULL,
                idp_discoveries             LONGTEXT DEFAULT NULL,
                INDEX idx_sso_provider_roles_type (type),
                INDEX idx_sso_provider_roles_entity_id (entity_id),
                UNIQUE INDEX idx_sso_provider_roles_entity_id_type (type, entity_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET UTF8
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE sso_provider_roles_eb6');
    }
}
