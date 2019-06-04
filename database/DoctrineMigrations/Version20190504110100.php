<?php

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * This is a combined migration of the previous migration versions:
 * - Version20180910134145
 * - Version20180910175453
 *
 * See: https://github.com/OpenConext/OpenConext-engineblock/pull/683
 *
 * They where previously part of the 5.10 release. But where added to 5.11 in order to make rolling updates more fluent.
 * As Version20190425205743 still was part of the 5.10 release. The two older versions have been merged into a new
 * migration.
 */
class Version20190504110100 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sso_provider_roles_eb5 DROP sps_entity_ids_without_consent');
        $this->addSql('ALTER TABLE sso_provider_roles_eb5 DROP attribute_aggregation_required');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sso_provider_roles_eb5 ADD sps_entity_ids_without_consent LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE sso_provider_roles_eb5 ADD attribute_aggregation_required TINYINT(1) DEFAULT NULL');
    }
}
