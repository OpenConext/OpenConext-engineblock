<?php

$config = array();

/**
 * Configuration for production environment
 * @todo Add URL to acceptance environment here
 *
 * Note: All other configurations SHOULD extend from this one 
 */
$config['production'] = array(
    'default_timezone' => 'Europe/Amsterdam',

    // TODO: sort out the minimal set of required Zend LDAP settings (for now these just work...)
    'ldap.host'                 => '',
    'ldap.useSsl'               => TRUE,
    'ldap.username'             => 'cn=engineblock,dc=coin,dc=surfnet,dc=nl',
    'ldap.password'             => '631E9383FD20',
    'ldap.bindRequiresDn'       => true,
    'ldap.accountDomainName'    => 'dev.coin.surf.net',
    'ldap.baseDn'               => 'dc=coin,dc=surfnet,dc=nl',

    'ServiceRegistry.Location'  => '',
);

/**
 * Configuration for acceptance environment
 * @todo Add URL to acceptance environment here
 */
$config['acceptance'] = array_merge($config['production'], array(
));

/**
 * Configuration for engineblock.coin.dev.surf.net
 */
$config['integration'] = array_merge($config['production'], array(
    'ldap.host'                 => 'coin-db.dev.coin.surf.net',

    'ServiceRegistry.Location'  => 'https://serviceregistry.dev.coin.surf.net/simplesaml/module.php/janus/rest.php',
));

/**
 * Configuration for Ibuildings VM
 */
$config['ebdev.net'] = array_merge($config['production'], array(

));