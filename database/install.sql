CREATE TABLE IF NOT EXISTS `consent` (
  `consent_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `usage_date` timestamp NOT NULL default '0000-00-00 00:00:00',
  `hashed_user_id` varchar(80) collate utf8_unicode_ci NOT NULL,
  `service_id` varchar(255) collate utf8_unicode_ci NOT NULL,
  `attribute` varchar(80) collate utf8_unicode_ci NOT NULL,
  UNIQUE KEY `hashed_user_id` (`hashed_user_id`,`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `uid` varchar(255) collate utf8_unicode_ci NOT NULL,
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `last_seen` datetime NOT NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_attributes` (
  `user_uid` varchar(255) collate utf8_unicode_ci NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `value` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`user_uid`,`name`,`value`),
  KEY `name` (`name`),
  KEY `user_uid` (`user_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
