<?php
// Add metadata_valid_until and metadata_cache_until fields for metadata refreshing.

/**
 * DbPatch makes the following variables available to PHP patches:
 *
 * @var $this       DbPatch_Command_Patch_PHP
 * @var $writer     DbPatch_Core_Writer
 * @var $db         Zend_Db_Adapter_Abstract
 * @var $phpFile    string
 */

$dbConfig = $db->getConfig();

$rows = $db->fetchAll(
    "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?",
    array(
         $dbConfig['dbname'],
         'janus__entity',
         'metadata_valid_until'
    )
);
if (count($rows) === 0) {
    $db->query("ALTER TABLE `janus__entity` ADD `metadata_valid_until` DATETIME NULL AFTER `metadataurl`");
}

$rows = $db->fetchAll(
    "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?",
    array(
         $dbConfig['dbname'],
         'janus__entity',
         'metadata_cache_until'
    )
);
if (count($rows) === 0) {
    $db->query("ALTER TABLE `janus__entity` ADD `metadata_cache_until` DATETIME NULL AFTER `metadata_valid_until`");
}
