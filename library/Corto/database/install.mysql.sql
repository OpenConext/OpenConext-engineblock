CREATE TABLE `consent` (
  `consent_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `usage_date` timestamp NOT NULL default '0000-00-00 00:00:00',
  `hashed_user_id` varchar(80) collate utf8_unicode_ci NOT NULL,
  `service_id` varchar(255) collate utf8_unicode_ci NOT NULL,
  `attribute` varchar(80) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`hashed_user_id`,`service_id`),
  KEY `hashed_user_id` (`hashed_user_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;