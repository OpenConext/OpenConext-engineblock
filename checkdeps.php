<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

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

