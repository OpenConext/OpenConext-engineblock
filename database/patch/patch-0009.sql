-- Add tables for new persistent NameID

CREATE TABLE IF NOT EXISTS `saml_persistent_id` (
  `persistent_id` char(40) NOT NULL COMMENT 'SHA1 of service_provider_uuid + user_uuid',
  `user_uuid` char(36) NOT NULL,
  `service_provider_uuid` char(36) NOT NULL,
  PRIMARY KEY  (`persistent_id`),
  KEY `user_uuid` (`user_uuid`,`service_provider_uuid`)
) ENGINE=InnoDB DEFAULT COLLATE=utf8_unicode_ci COMMENT='Look up table for persistent_ids we hand out';

CREATE TABLE IF NOT EXISTS `service_provider_uuid` (
  `uuid` char(36) NOT NULL,
  `service_provider_entity_id` varchar(1024) NOT NULL,
  PRIMARY KEY  (`uuid`),
  KEY `service_provider_entity_id` (`service_provider_entity_id`(767))
) ENGINE=InnoDB DEFAULT COLLATE=utf8_unicode_ci COMMENT='Lookup table for UUIDs for Service Providers, provides a level of indirection in case a logical SP decides to switch Entity IDs';
