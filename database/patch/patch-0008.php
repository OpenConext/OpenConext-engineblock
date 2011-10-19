<?php
// Remove all usertypes other than technical and admin

/**
 * DbPatch makes the following variables available to PHP patches:
 *
 * @var $this       DbPatch_Command_Patch_PHP
 * @var $writer     DbPatch_Core_Writer
 * @var $db         Zend_Db_Adapter_Abstract
 * @var $phpFile    string
 */

$userRows = $db->fetchAll("SELECT * FROM janus__user");
foreach ($userRows as $userRow) {
    $userTypes = unserialize($userRow['type']);
    foreach ($userTypes as $i => $userType) {
        if (!in_array($userType, array('technical', 'admin'))) {
            unset ($userTypes[$i]);
        }
    }
    rsort($userTypes);
    $db->query(
        'UPDATE janus__user SET `type` = ? WHERE uid = ?',
        array(
            serialize($userTypes),
            $userRow['uid'],
        )
    );
}