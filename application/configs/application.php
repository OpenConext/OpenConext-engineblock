<?php

/**
 * @var $config Configuration array construct
 */

$config = array();

/**
 * Configuration for production environment
 * @todo Add URL to acceptance environment here
 *
 * Note: All other configurations SHOULD extend from this one 
 */
$config['production'] = array(
    'Php.DefaultTimezone' => 'Europe/Amsterdam',
    'Php.DisplayErrors'   => false,
    'Php.ErrorReporting'  => E_ALL,

    'PublicKey' => '-----BEGIN CERTIFICATE-----
MIICgTCCAeoCCQCbOlrWDdX7FTANBgkqhkiG9w0BAQUFADCBhDELMAkGA1UEBhMC
Tk8xGDAWBgNVBAgTD0FuZHJlYXMgU29sYmVyZzEMMAoGA1UEBxMDRm9vMRAwDgYD
VQQKEwdVTklORVRUMRgwFgYDVQQDEw9mZWlkZS5lcmxhbmcubm8xITAfBgkqhkiG
9w0BCQEWEmFuZHJlYXNAdW5pbmV0dC5ubzAeFw0wNzA2MTUxMjAxMzVaFw0wNzA4
MTQxMjAxMzVaMIGEMQswCQYDVQQGEwJOTzEYMBYGA1UECBMPQW5kcmVhcyBTb2xi
ZXJnMQwwCgYDVQQHEwNGb28xEDAOBgNVBAoTB1VOSU5FVFQxGDAWBgNVBAMTD2Zl
aWRlLmVybGFuZy5ubzEhMB8GCSqGSIb3DQEJARYSYW5kcmVhc0B1bmluZXR0Lm5v
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDivbhR7P516x/S3BqKxupQe0LO
NoliupiBOesCO3SHbDrl3+q9IbfnfmE04rNuMcPsIxB161TdDpIesLCn7c8aPHIS
KOtPlAeTZSnb8QAu7aRjZq3+PbrP5uW3TcfCGPtKTytHOge/OlJbo078dVhXQ14d
1EDwXJW1rRXuUt4C8QIDAQABMA0GCSqGSIb3DQEBBQUAA4GBACDVfp86HObqY+e8
BUoWQ9+VMQx1ASDohBjwOsg2WykUqRXF+dLfcUH9dWR63CtZIKFDbStNomPnQz7n
bK+onygwBspVEbnHuUihZq3ZUdmumQqCw4Uvs/1Uvq3orOo/WJVhTyvLgFVK2Qar
Q4/67OZfHd7R+POBXhophSMv1ZOo
-----END CERTIFICATE-----',
    'PrivateKey' => '-----BEGIN RSA PRIVATE KEY-----
MIICXgIBAAKBgQDivbhR7P516x/S3BqKxupQe0LONoliupiBOesCO3SHbDrl3+q9
IbfnfmE04rNuMcPsIxB161TdDpIesLCn7c8aPHISKOtPlAeTZSnb8QAu7aRjZq3+
PbrP5uW3TcfCGPtKTytHOge/OlJbo078dVhXQ14d1EDwXJW1rRXuUt4C8QIDAQAB
AoGAD4/Z4LWVWV6D1qMIp1Gzr0ZmdWTE1SPdZ7Ej8glGnCzPdguCPuzbhGXmIg0V
J5D+02wsqws1zd48JSMXXM8zkYZVwQYIPUsNn5FetQpwxDIMPmhHg+QNBgwOnk8J
K2sIjjLPL7qY7Itv7LT7Gvm5qSOkZ33RCgXcgz+okEIQMYkCQQDzbTOyDL0c5WQV
6A2k06T/azdhUdGXF9C0+WkWSfNaovmTgRXh1G+jMlr82Snz4p4/STt7P/XtyWzF
3pkVgZr3AkEA7nPjXwHlttNEMo6AtxHd47nizK2NUN803ElIUT8P9KSCoERmSXq6
6PDekGNic4ldpsSvOeYCk8MAYoDBy9kvVwJBAMLgX4xg6lzhv7hR5+pWjTb1rIY6
rCHbrPfU264+UZXz9v2BT/VUznLF81WMvStD9xAPHpFS6R0OLghSZhdzhI0CQQDL
8Duvfxzrn4b9QlmduV8wLERoT6rEVxKLsPVz316TGrxJvBZLk/cV0SRZE1cZf4uk
XSWMfEcJ/0Zt+LdG1CqjAkEAqwLSglJ9Dy3HpgMz4vAAyZWzAxvyA1zW0no9GOLc
PQnYaNUN/Fy2SYtETXTb0CQ9X1rt8ffkFP7ya+5TC83aMg==
-----END RSA PRIVATE KEY-----',

    // TODO: sort out the minimal set of required Zend LDAP settings (for now these just work...)
    'ldap.host'                 => '',
    'ldap.useSsl'               => TRUE,
    'ldap.username'             => 'cn=engineblock,dc=coin,dc=surfnet,dc=nl',
    'ldap.password'             => '631E9383FD20',
    'ldap.bindRequiresDn'       => true,
    'ldap.accountDomainName'    => 'dev.coin.surf.net',
    'ldap.baseDn'               => 'dc=coin,dc=surfnet,dc=nl',

    'ServiceRegistry.Location'  => '',

    'Grouper.Protocol'          => 'https',
    'Grouper.Host'              => '',
    'Grouper.User'              => 'engineblock',
    'Grouper.Password'          => '',
    'Grouper.Path'              => '/grouper-ws/servicesRest',
    'Grouper.Version'           => 'v1_6_000',

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
    'Php.DisplayErrors'   => true,
    'Php.ErrorReporting'  => E_ALL - E_NOTICE,

    'ldap.host'                 => 'coin-db.dev.coin.surf.net',

    'ServiceRegistry.Location'  => 'https://serviceregistry.dev.coin.surf.net/simplesaml/module.php/janus/rest.php',
));

/**
 * Note: Add long-lived development environments after here.
 */

/**
 * Configuration for Ibuildings VM
 */
$config['ebdev.net'] = array_merge($config['production'], array(
    'ServiceRegistry.Location'  => 'https://serviceregistry.ebdev.net/simplesaml/module.php/janus/rest.php',

    'Php.DisplayErrors'   => true,
));

/**
 * Configuration for Ivo's localhost
 */
$config['ivodev'] = array_merge($config['production'], array(
    'ldap.host'   => 'coin-db.dev.coin.surf.net',
    'ldap.useSsl' => FALSE,
    'ServiceRegistry.Location'  => 'http://simplesamlphp.local:10088/simplesaml/module.php/janus/rest.php',
    'Grouper.Host'              => 'coin-db.dev.coin.surf.net',
    'Grouper.User'              => 'engineblock',
    'Grouper.Password'          => '631E9383FD20',
    

));

/**
 * Note: Add one-off dev environments in application.local.php
 */
if (file_exists('application.local.php')) {
    @include 'application.local.php';
}

