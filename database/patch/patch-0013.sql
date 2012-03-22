-- Add table for storing the ACL Group provider information for service providers
CREATE TABLE `service_provider_group_acl` (
  `id` bigint(20) NOT NULL auto_increment,
  `group_provider_id` bigint(20) NOT NULL,
  `spentityid` varchar(1024) collate utf8_unicode_ci NOT NULL,
  `allow_groups` tinyint(1) default '0',
  `allow_members` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `spentityid_group_provider_id` (`spentityid`(250), group_provider_id)
) ENGINE=InnoDB COLLATE=utf8_unicode_ci;