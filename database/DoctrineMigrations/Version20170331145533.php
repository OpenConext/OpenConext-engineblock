<?php

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170331145533 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE IF EXISTS log_logins');
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `log_logins` (`loginstamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, `userid` varchar(1000) NOT NULL, `spentityid` varchar(1000) DEFAULT NULL, `idpentityid` varchar(1000) DEFAULT NULL, `spentityname` varchar(1000) DEFAULT NULL, `idpentityname` varchar(1000) DEFAULT NULL, `useragent` varchar(1024) DEFAULT NULL, `voname` varchar(1024) DEFAULT NULL, `keyid` varchar(50) DEFAULT NULL, `id` int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
    }
}
