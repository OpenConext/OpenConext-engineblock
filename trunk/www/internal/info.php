<?php

/**
 * Define application environment
 */
defined('ENGINEBLOCK_ENV')
    || define('ENGINEBLOCK_ENV',
              (getenv('ENGINEBLOCK_ENV') ? getenv('ENGINEBLOCK_ENV')
                                         : 'production'));

require '../../library/EngineBlock/ApplicationSingleton.php';

$application = EngineBlock_ApplicationSingleton::getInstance();
$config = $application->getConfiguration();
echo "Environment: " . ENGINEBLOCK_ENV . '<br />' . PHP_EOL;
echo "Configuration values used:" . PHP_EOL;
var_dump($config->toArray());
