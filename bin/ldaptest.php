#!/bin/env php
<?php
ini_set('display_errors', true);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

#-D cn=admin,cn=config -h localhost -w "$OC__LDAP_PASS"
$ldap = new Zend_Ldap(array(
    'host' => 'localhost',
    'username' => 'cn=admin,cn=config',
    'password' => 'ldappass'
));
$schemasFound = $ldap->search('(objectClass=olcSchemaConfig)', 'cn=schema,cn=config')->toArray();

$schemasDefined = require __DIR__ . '/../ldap/schemas.php';
foreach ($schemasDefined as $schemaDefined) {
    foreach ($schemasFound as $schemaFound) {
        if (!strstr($schemaFound['dn'], $schemaDefined['cn'][0] . ',cn=schema,cn=config')) {
            continue;
        }
//        var_dump($schemaFound['olcattributetypes']);
//        var_dump($schemaDefined['olcattributetypes']);
        $attributeDifference = array_diff($schemaDefined['olcattributetypes'], $schemaFound['olcattributetypes']);
        if (empty($attributeDifference)) {
            echo "{$schemaFound['dn']} is good!" . PHP_EOL;
            continue 2;
        }

        unset($schemaDefined['dn'], $schemaDefined['cn']);
        $ldap->save($schemaFound['dn'], $schemaDefined);
        echo "Fixed {$schemaFound['dn']}" . PHP_EOL;
    }
    echo PHP_EOL;
}
//foreach ($schemas as $schema) {
//
//}


//$ldap->update();
