<?php declare(strict_types=1);

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration adds the mdui column, a collection
 * of mdui elements (displayname, description, keywords,
 * logo, privacy statement url,..). The column is typed
 * to be a DoctrinType: engineblock_metadata_mdui
 *
 * Note that the existing multilingual mdui columns that
 * exist on the sso_provider_roles_eb5 are not yet removed
 * That will be done next release. This ensures we can roll
 * back to the previous situation if need be.
 */
final class Version20230418113623 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sso_provider_roles_eb5 ADD mdui LONGTEXT NOT NULL COMMENT \'(DC2Type:engineblock_metadata_mdui)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sso_provider_roles_eb5 DROP mdui');
    }
}
