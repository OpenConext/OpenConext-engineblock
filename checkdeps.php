<?php

test(strnatcmp(phpversion(), '5.2.10') >= 0, "PHP > 5.2.10");
test(ini_get('short_open_tag')      , "PHP.ini 'short_open_tag' should be on");
test(extension_loaded('mysql')      , "Extension loaded: Mysql");
test(extension_loaded('memcache')   , "Extension loaded: Memcache");
test(extension_loaded('ldap')       , "Extension loaded: Ldap");
test(extension_loaded('xml')        , "Extension loaded: xml");
if (extension_loaded('xml')) {
    test(
        class_exists('XMLWriter', true),
        "XMLWriter should be available (hint: php-xml package)"
    );
}

function test($value, $description) {
    if ($value) {
        echo "[PASS] ";
    }
    else {
        echo "[FAIL] ";
    }
    echo $description . PHP_EOL;
}


/**
 * * Apache with modules:
** mod_php
* PHP 5.2.x with modules:
** memcache
** ldap
** libxml
* Java > 1.5
* MySQL > 5.x with settings:
** default-storage-engine=InnoDB (recommended)
** default-collation=utf8_unicode_ci (recommended)
* Memcached
* LDAP
* Grouper
* Service Registry
* wget
 */

