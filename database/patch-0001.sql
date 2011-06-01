-- Service Registry patch for refreshing metadata based on validUntil and cacheUntil
-- in SAML2 metadata XML.
ALTER TABLE `janus__entity` ADD `metadata_valid_until` DATETIME NULL AFTER `metadataurl` ,
ADD `metadata_cache_until` DATETIME NULL AFTER `metadata_valid_until`