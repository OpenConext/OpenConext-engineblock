-- Initial migration away from LiquiBase

DROP TABLE `DATABASECHANGELOG`;
DROP TABLE `DATABASECHANGELOGLOCK`;


CREATE TABLE IF NOT EXISTS `consent` (
  `consent_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `usage_date` timestamp NOT NULL default '0000-00-00 00:00:00',
  `hashed_user_id` varchar(80) NOT NULL,
  `service_id` varchar(255) NOT NULL,
  `attribute` varchar(80) NOT NULL,
  PRIMARY KEY  (`hashed_user_id`,`service_id`),
  KEY `hashed_user_id` (`hashed_user_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `DATABASECHANGELOG`
--

CREATE TABLE IF NOT EXISTS `DATABASECHANGELOG` (
  `ID` varchar(63) NOT NULL,
  `AUTHOR` varchar(63) NOT NULL,
  `FILENAME` varchar(200) NOT NULL,
  `DATEEXECUTED` datetime NOT NULL,
  `ORDEREXECUTED` int(11) NOT NULL,
  `EXECTYPE` varchar(10) NOT NULL,
  `MD5SUM` varchar(35) default NULL,
  `DESCRIPTION` varchar(255) default NULL,
  `COMMENTS` varchar(255) default NULL,
  `TAG` varchar(255) default NULL,
  `LIQUIBASE` varchar(20) default NULL,
  PRIMARY KEY  (`ID`,`AUTHOR`,`FILENAME`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `DATABASECHANGELOGLOCK`
--

CREATE TABLE IF NOT EXISTS `DATABASECHANGELOGLOCK` (
  `ID` int(11) NOT NULL,
  `LOCKED` tinyint(1) NOT NULL,
  `LOCKGRANTED` datetime default NULL,
  `LOCKEDBY` varchar(255) default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `db_changelog`
--

CREATE TABLE IF NOT EXISTS `db_changelog` (
  `patch_number` int(11) NOT NULL,
  `branch` varchar(50) NOT NULL,
  `completed` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `filename` varchar(100) NOT NULL,
  `hash` varchar(32) NOT NULL,
  `description` varchar(200) default NULL,
  PRIMARY KEY  (`patch_number`,`branch`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `group_provider`
--

CREATE TABLE IF NOT EXISTS `group_provider` (
  `id` int(11) NOT NULL auto_increment,
  `identifier` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `classname` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_provider_decorator`
--

CREATE TABLE IF NOT EXISTS `group_provider_decorator` (
  `id` int(11) NOT NULL auto_increment,
  `group_provider_id` int(11) NOT NULL,
  `classname` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_provider_decorator_option`
--

CREATE TABLE IF NOT EXISTS `group_provider_decorator_option` (
  `group_provider_decorator_id` int(11) NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `value` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`group_provider_decorator_id`,`name`)
) ENGINE=InnoDB COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_provider_filter`
--

CREATE TABLE IF NOT EXISTS `group_provider_filter` (
  `id` int(11) NOT NULL auto_increment,
  `group_provider_id` int(11) NOT NULL,
  `type` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `classname` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_provider_filter_option`
--

CREATE TABLE IF NOT EXISTS `group_provider_filter_option` (
  `group_provider_filter_id` int(11) NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `value` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`group_provider_filter_id`,`name`)
) ENGINE=InnoDB COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_provider_option`
--

CREATE TABLE IF NOT EXISTS `group_provider_option` (
  `group_provider_id` int(11) NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `value` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`group_provider_id`,`name`)
) ENGINE=InnoDB COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_provider_precondition`
--

CREATE TABLE IF NOT EXISTS `group_provider_precondition` (
  `id` int(11) NOT NULL auto_increment,
  `group_provider_id` int(11) NOT NULL,
  `classname` varchar(255) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_provider_precondition_option`
--

CREATE TABLE IF NOT EXISTS `group_provider_precondition_option` (
  `group_provider_precondition_id` int(11) NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `value` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`group_provider_precondition_id`,`name`)
) ENGINE=InnoDB COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_provider_user_oauth`
--

CREATE TABLE IF NOT EXISTS `group_provider_user_oauth` (
  `provider_id` varchar(255) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `oauth_token` varchar(1024) NOT NULL,
  `oauth_secret` varchar(1024) NOT NULL,
  PRIMARY KEY  (`provider_id`,`user_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `log_logins`
--

CREATE TABLE IF NOT EXISTS `log_logins` (
  `loginstamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `userid` varchar(1000) NOT NULL,
  `spentityid` varchar(1000) default NULL,
  `idpentityid` varchar(1000) default NULL,
  `spentityname` varchar(1000) default NULL,
  `idpentityname` varchar(1000) default NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `virtual_organisation`
--

CREATE TABLE IF NOT EXISTS `virtual_organisation` (
  `vo_id` varchar(255) NOT NULL,
  `vo_type` enum('GROUP','STEM','IDP') NOT NULL,
  PRIMARY KEY  (`vo_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `virtual_organisation_attribute`
--

CREATE TABLE IF NOT EXISTS `virtual_organisation_attribute` (
  `id` int(11) NOT NULL auto_increment,
  `vo_id` varchar(255) NOT NULL,
  `sp_entity_id` varchar(1024) NOT NULL,
  `user_id_pattern` varchar(255) NOT NULL,
  `attribute_name_saml` varchar(255) NOT NULL,
  `attribute_name_opensocial` varchar(255) NOT NULL,
  `attribute_value` varchar(1024) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `vo_id` (`vo_id`(200),`sp_entity_id`(200),`user_id_pattern`(200),`attribute_name_saml`(200),`attribute_name_opensocial`(200))
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `virtual_organisation_group`
--

CREATE TABLE IF NOT EXISTS `virtual_organisation_group` (
  `vo_id` varchar(255) NOT NULL,
  `group_id` varchar(255) NOT NULL,
  PRIMARY KEY  (`vo_id`,`group_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `virtual_organisation_idp`
--

CREATE TABLE IF NOT EXISTS `virtual_organisation_idp` (
  `vo_id` varchar(255) NOT NULL,
  `idp_id` varchar(255) NOT NULL,
  PRIMARY KEY  (`vo_id`,`idp_id`)
) ENGINE=InnoDB;
