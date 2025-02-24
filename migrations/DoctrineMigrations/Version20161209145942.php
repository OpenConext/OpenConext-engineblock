<?php

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161209145942 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // Make sure consent_date is not updated every time a row is updated
        $this->addSql('ALTER TABLE consent CHANGE consent_date consent_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE consent CHANGE consent_date consent_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;');

    }
}
