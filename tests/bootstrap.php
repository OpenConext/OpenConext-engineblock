<?php

define('TEST_RESOURCES_DIR', dirname(__FILE__) . '/resources');

ini_set('date.timezone', 'Europe/Amsterdam');

// Include composer autoloader, this intentionally included instead of required since CI system does not
// use composer and will fail on requiring a non-existent autoload file
$rootDir = realpath(__DIR__ . '/../');
require_once $rootDir . '/vendor/autoload.php';

$application = EngineBlock_ApplicationSingleton::getInstance();

$application->setLogInstance(new Psr\Log\NullLogger());

$config = new Zend_Config_Ini(
    ENGINEBLOCK_FOLDER_APPLICATION . EngineBlock_Application_Bootstrapper::CONFIG_FILE_DEFAULT,
    'base',
    array('allowModifications' => true)
);
$config->testing = true;
$config->hostname = 'engine.demo.openconext.org';
$application->setConfiguration($config);
$application->bootstrap();
