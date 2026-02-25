<?php

/**
 * Copyright 2026 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;

/**
 * Baseline migration - Creates initial database schema based on production state (6.18) as of feb 2026
 *
 * This migration represents a "squashed" baseline that replaces all previous migrations.
 * It creates all required tables from scratch for new installations, and gracefully skips
 * execution on existing databases where tables are already present.
 */
final class Version20260210000000 extends AbstractEngineBlockMigration
{
    public function getDescription(): string
    {
        return 'Baseline migration: Creates all database tables (consent, saml_persistent_id, service_provider_uuid, sso_provider_roles_eb5, user). Skips if tables already exist.';
    }

    public function preUp(Schema $schema): void
    {
        parent::preUp($schema);

        $tables = $this->sm->listTableNames();
        $this->skipIf(
            in_array('sso_provider_roles_eb5', $tables, true),
            'Database schema already exists (found sso_provider_roles_eb5 table). Skipping baseline migration.'
        );
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `consent` (
            `consent_date` datetime NOT NULL,
            `hashed_user_id` varchar(80) NOT NULL,
            `service_id` varchar(255) NOT NULL,
            `attribute` varchar(80) NOT NULL,
            `consent_type` varchar(20) DEFAULT \'explicit\',
            `deleted_at` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
            PRIMARY KEY (`hashed_user_id`,`service_id`,`deleted_at`),
            KEY `hashed_user_id` (`hashed_user_id`),
            KEY `service_id` (`service_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci');

        $this->addSql('CREATE TABLE `saml_persistent_id` (
            `persistent_id` char(40) NOT NULL COMMENT \'SHA1 of service_provider_uuid + user_uuid\',
            `user_uuid` char(36) NOT NULL,
            `service_provider_uuid` char(36) NOT NULL,
            PRIMARY KEY (`persistent_id`),
            KEY `user_uuid` (`user_uuid`,`service_provider_uuid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT=\'Look up table for persistent_ids we hand out\'');

        $this->addSql('CREATE TABLE `service_provider_uuid` (
            `uuid` char(36) NOT NULL,
            `service_provider_entity_id` varchar(1024) NOT NULL,
            PRIMARY KEY (`uuid`),
            KEY `service_provider_entity_id` (`service_provider_entity_id`(255))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT=\'Lookup table for UUIDs for Service Providers, provides a lev\'');

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

        $this->addSql('CREATE TABLE `user` (
            `collab_person_id` varchar(255) NOT NULL COMMENT \'(DC2Type:engineblock_collab_person_id)\',
            `uuid` char(36) NOT NULL COMMENT \'(DC2Type:engineblock_collab_person_uuid)\',
            PRIMARY KEY (`collab_person_id`),
            KEY `idx_user_uuid` (`uuid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci');

        // Note: The migration_versions table is managed automatically by Doctrine Migrations
    }

    public function isTransactional(): bool
    {
        // This migration uses implicit commits only, so disable transactions in doctrine to clarify our intent.
        // https://mariadb.com/docs/server/reference/sql-statements/transactions/sql-statements-that-cause-an-implicit-commit

        return false;
    }
}
