Requires:
- Janus module
- Cron module

After enabling, install this patch:

ALTER TABLE `janus__entity` ADD `metadata_valid_until` DATETIME NULL AFTER `metadataurl` ,
ADD `metadata_cache_until` DATETIME NULL AFTER `metadata_valid_until` 