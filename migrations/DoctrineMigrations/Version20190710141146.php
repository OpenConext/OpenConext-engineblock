<?php

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190710141146 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sso_provider_roles_eb5 DROP disable_scoping, DROP additional_logging, DROP signature_method, DROP is_transparent_issuer, DROP is_trusted_proxy, DROP display_unconnected_idps_wayf, DROP is_consent_required, DROP terms_of_service_url, DROP skip_denormalization, DROP policy_enforcement_decision_required, DROP guest_qualifier, DROP schac_home_organization, DROP hidden, DROP requesterid_required, DROP sign_response');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sso_provider_roles_eb5 ADD disable_scoping TINYINT(1) NOT NULL, ADD additional_logging TINYINT(1) NOT NULL, ADD signature_method VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD is_transparent_issuer TINYINT(1) DEFAULT NULL, ADD is_trusted_proxy TINYINT(1) DEFAULT NULL, ADD display_unconnected_idps_wayf TINYINT(1) DEFAULT NULL, ADD is_consent_required TINYINT(1) DEFAULT NULL, ADD terms_of_service_url VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD skip_denormalization TINYINT(1) DEFAULT NULL, ADD policy_enforcement_decision_required TINYINT(1) DEFAULT NULL, ADD guest_qualifier VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD schac_home_organization VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD hidden TINYINT(1) DEFAULT NULL, ADD requesterid_required TINYINT(1) DEFAULT NULL, ADD sign_response TINYINT(1) DEFAULT NULL');
    }
}
