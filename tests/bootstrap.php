<?php

define('TEST_RESOURCES_DIR', dirname(__FILE__) . '/resources');

require_once realpath(__DIR__) . '/../app/bootstrap.php.cache';

$application = EngineBlock_ApplicationSingleton::getInstance();

$config = new Zend_Config_Ini(
    ENGINEBLOCK_FOLDER_APPLICATION . EngineBlock_Application_Bootstrapper::CONFIG_FILE_DEFAULT,
    'base',
    array('allowModifications' => true)
);
$config->testing = true;

$application->setConfiguration($config);
$application->bootstrap(new Psr\Log\NullLogger(), 'requestId');
