<?php

/**
 * Helper script for Liquibase.
 *
 * If the ENGINEBLOCK_ENV environment variable is set then this will be able to
 * return properties for the first database master server configured.
 * Which can then be used to apply updates.
 */

// Input validation / filtering
$AVAILABLE_PROPERTIES = array('dbname', 'host', 'user', 'password');
$requestedProperty = $argv[1];
if (!in_array($requestedProperty, $AVAILABLE_PROPERTIES)) {
    exit("Please use one of the following properties: " . implode(', ', $AVAILABLE_PROPERTIES) . PHP_EOL);
}

// Define application environment
defined('ENGINEBLOCK_ENV')
    || define('ENGINEBLOCK_ENV',
              (getenv('ENGINEBLOCK_ENV') ? getenv('ENGINEBLOCK_ENV')
                                         : 'production'));

// Load up EngineBlock Application so it can load the configuration for us
require '../library/EngineBlock/ApplicationSingleton.php';
$application = EngineBlock_ApplicationSingleton::getInstance();
$application->bootstrap(ENGINEBLOCK_ENV);

// Get configuration and see if we have database configuration with masters
$configuration = $application->getConfiguration();
if (!isset($configuration->database)) {
    exit("No database configuration for environment '" . ENGINEBLOCK_ENV . "'" . PHP_EOL);
}
if (!isset($configuration->database->masters)) {
    exit("No database masters configured for environment '" . ENGINEBLOCK_ENV . "'?!" . PHP_EOL);
}

// Retrieve the settings for the first master
$masterServers = $configuration->database->masters->toArray();
$randomMasterServerKey = 0;
$randomMasterServerName = $masterServers[$randomMasterServerKey];
if (!isset($configuration->database->$randomMasterServerName)) {
    exit("Unable to use database.$randomMasterServerName for connection?!" . PHP_EOL);
}
$randomMasterServerSettings = $configuration->database->$randomMasterServerName;

// Convert settings to a (writable) stdClass
$server = new stdClass();
foreach ($randomMasterServerSettings as $key => $value) {
    $server->$key = $value;
}

// Parse the DSN and make it available as properties
$dsnProperties = engineBlockGetPDODsnProperties($randomMasterServerSettings->dsn);
foreach ($dsnProperties as $dsnPropertyKey => $dsnPropertyValue) {
    $server->$dsnPropertyKey = $dsnPropertyValue;
}

// Echo the property and exit
echo $server->$requestedProperty;
exit(0);

function engineBlockGetPDODsnProperties($dsn)
{
    $driver     = substr($dsn, 0, strpos($dsn, ':'));
    $propertiesString = substr($dsn, strpos($dsn, ':') + 1);
    $propertiesArray = explode(';', $propertiesString);
    $properties = array(
        'driver' => $driver,
    );
    foreach ($propertiesArray as $property) {
        $propertyParts = explode('=', $property);
        $propertyName = array_shift($propertyParts);
        $propertyValue = implode('=', $propertyParts);
        $properties[$propertyName] = $propertyValue;
    }
    return $properties;
}
