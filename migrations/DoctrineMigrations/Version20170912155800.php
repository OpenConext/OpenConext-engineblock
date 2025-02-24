<?php

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170912155800 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS group_provider');
        $this->addSql('DROP TABLE IF EXISTS group_provider_decorator');
        $this->addSql('DROP TABLE IF EXISTS group_provider_decorator_option');
        $this->addSql('DROP TABLE IF EXISTS group_provider_filter');
        $this->addSql('DROP TABLE IF EXISTS group_provider_filter_option');
        $this->addSql('DROP TABLE IF EXISTS group_provider_option');
        $this->addSql('DROP TABLE IF EXISTS group_provider_precondition');
        $this->addSql('DROP TABLE IF EXISTS group_provider_precondition_option');
        $this->addSql('DROP TABLE IF EXISTS group_provider_user_oauth');
        $this->addSql('DROP TABLE IF EXISTS service_provider_group_acl');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("CREATE TABLE `group_provider` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `classname` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `logo_url` varchar(1024) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
        $this->addSql("CREATE TABLE `group_provider_decorator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_provider_id` int(11) NOT NULL,
  `classname` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
        $this->addSql("CREATE TABLE `group_provider_decorator_option` (
  `group_provider_decorator_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`group_provider_decorator_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
        $this->addSql("CREATE TABLE `group_provider_filter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_provider_id` int(11) NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `classname` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
        $this->addSql("CREATE TABLE `group_provider_filter_option` (
  `group_provider_filter_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`group_provider_filter_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
        $this->addSql("CREATE TABLE `group_provider_option` (
  `group_provider_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`group_provider_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
        $this->addSql("CREATE TABLE `group_provider_precondition` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_provider_id` int(11) NOT NULL,
  `classname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
        $this->addSql("CREATE TABLE `group_provider_precondition_option` (
  `group_provider_precondition_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`group_provider_precondition_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
        $this->addSql("CREATE TABLE `group_provider_user_oauth` (
  `provider_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `oauth_token` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `oauth_secret` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`provider_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
        $this->addSql("CREATE TABLE `service_provider_group_acl` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `group_provider_id` bigint(20) NOT NULL,
  `spentityid` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `allow_groups` tinyint(1) DEFAULT '0',
  `allow_members` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `spentityid_group_provider_id` (`spentityid`(250),`group_provider_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");
    }
}
