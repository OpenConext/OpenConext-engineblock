<?php
// Add urn:collab:group: prefixing to Grouper group providers

/**
 * DbPatch makes the following variables available to PHP patches:
 *
 * @var $this       DbPatch_Command_Patch_PHP
 * @var $writer     DbPatch_Core_Writer
 * @var $db         Zend_Db_Adapter_Abstract
 * @var $phpFile    string
 */

$grouperProviders = $db->fetchAll(
    "SELECT * FROM group_provider WHERE classname = 'EngineBlock_Group_Provider_Grouper'"
);

foreach ($grouperProviders as $grouperProvider) {
    ////////////////////////////
    // Modify Group ID OUT (Decorator)
    ////////////////////////////
    $statement = $db->query("
        INSERT INTO group_provider_decorator (group_provider_id, classname)
        VALUES (?, ?)",
        array(
            $grouperProvider['id'],
            'EngineBlock_Group_Provider_Decorator_GroupIdReplace',
        )
    );
    $decoratorId = $db->lastInsertId();

    $statement = $db->query("
        INSERT INTO group_provider_decorator_option (group_provider_decorator_id, `name`, `value`)
        VALUES (?,?,?),(?,?,?)",
        array(
            $decoratorId,
            'search',
            '|urn:collab:group:(.+)|',
            
            $decoratorId,
            'replace',
            '$1',
        )
    );

    ////////////////////////////
    // Modify Group IN (Filter)
    ////////////////////////////
    $statement = $db->query("
        INSERT INTO group_provider_filter (group_provider_id, `type`, classname)
        VALUES (?, ?, ?)",
        array(
            $grouperProvider['id'],
            'group',
            'EngineBlock_Group_Provider_Filter_ModelProperty_PregReplace',
        )
    );
    $filterId = $db->lastInsertId();

    $statement = $db->query("
        INSERT INTO group_provider_filter_option (group_provider_filter_id, `name`, `value`)
        VALUES (?,?,?),(?,?,?),(?,?,?)",
        array(
             $filterId,
             'property',
             'id',

            $filterId,
            'search',
            '|(.+)|',

            $filterId,
            'replace',
            'urn:collab:group:$1',
        )
    );
}
