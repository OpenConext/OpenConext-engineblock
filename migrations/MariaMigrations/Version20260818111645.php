<?php

declare(strict_types=1);

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260818111645 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop sso_provider_roles_eb5 table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE sso_provider_roles_eb5');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `sso_provider_roles_eb5` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `entity_id` varchar(255) NOT NULL,
            `name_nl` varchar(255) NOT NULL,
            `name_en` varchar(255) NOT NULL,
            `name_pt` varchar(255) NOT NULL,
            `description_nl` varchar(255) NOT NULL,
            `description_en` varchar(255) NOT NULL,
            `description_pt` varchar(255) NOT NULL,
            `display_name_nl` varchar(255) NOT NULL,
            `display_name_en` varchar(255) NOT NULL,
            `display_name_pt` varchar(255) NOT NULL,
            `logo` longtext NOT NULL COMMENT \'(DC2Type:object)\',
            `organization_nl_name` text DEFAULT NULL COMMENT \'(DC2Type:object)\',
            `organization_en_name` text DEFAULT NULL COMMENT \'(DC2Type:object)\',
            `organization_pt_name` text DEFAULT NULL COMMENT \'(DC2Type:object)\',
            `keywords_nl` varchar(255) NOT NULL,
            `keywords_en` varchar(255) NOT NULL,
            `keywords_pt` varchar(255) NOT NULL,
            `certificates` text NOT NULL COMMENT \'(DC2Type:array)\',
            `workflow_state` varchar(255) NOT NULL,
            `contact_persons` text NOT NULL COMMENT \'(DC2Type:array)\',
            `name_id_format` varchar(255) DEFAULT NULL,
            `name_id_formats` text NOT NULL COMMENT \'(DC2Type:array)\',
            `single_logout_service` text DEFAULT NULL COMMENT \'(DC2Type:object)\',
            `requests_must_be_signed` tinyint(1) NOT NULL,
            `manipulation` text NOT NULL,
            `type` varchar(255) NOT NULL,
            `attribute_release_policy` text DEFAULT NULL COMMENT \'(DC2Type:array)\',
            `assertion_consumer_services` text DEFAULT NULL COMMENT \'(DC2Type:array)\',
            `allowed_idp_entity_ids` mediumtext DEFAULT NULL COMMENT \'(DC2Type:array)\',
            `allow_all` tinyint(1) DEFAULT NULL,
            `requested_attributes` text DEFAULT NULL COMMENT \'(DC2Type:array)\',
            `enabled_in_wayf` tinyint(1) DEFAULT NULL,
            `single_sign_on_services` text DEFAULT NULL COMMENT \'(DC2Type:array)\',
            `shib_md_scopes` text DEFAULT NULL COMMENT \'(DC2Type:array)\',
            `support_url_en` varchar(255) DEFAULT NULL,
            `support_url_pt` varchar(255) DEFAULT NULL,
            `support_url_nl` varchar(255) DEFAULT NULL,
            `consent_settings` longtext DEFAULT NULL COMMENT \'(DC2Type:json)\',
            `coins` longtext NOT NULL COMMENT \'(DC2Type:engineblock_metadata_coins)\',
            `mdui` longtext NOT NULL COMMENT \'(DC2Type:engineblock_metadata_mdui)\',
            `idp_discoveries` longtext DEFAULT NULL COMMENT \'(DC2Type:json)\',
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_sso_provider_roles_entity_id_type` (`type`,`entity_id`),
            KEY `idx_sso_provider_roles_type` (`type`),
            KEY `idx_sso_provider_roles_entity_id` (`entity_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=63268 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci');
    }
}
