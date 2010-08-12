<?php

$config = array(
    'default_timezone' => 'Europe/Amsterdam',

    // TODO: sort out the minimal set of required Zend LDAP settings (for now these just work...)
    'ldap' => array(
    	'host'              => 'coin-db.dev.coin.surf.net',
        'useSsl'			=> TRUE,
    	'username'          => 'cn=engineblock,dc=coin,dc=surfnet,dc=nl',
    	'password'          => '631E9383FD20',
    	'bindRequiresDn'    => true,
    	'accountDomainName' => 'dev.coin.surf.net',
    	'baseDn'            => 'dc=coin,dc=surfnet,dc=nl',
    ),

);