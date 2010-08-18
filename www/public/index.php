<?php

ini_set('display_errors', false);
error_reporting(E_ALL);

if(!function_exists('spl_autoload_register')) {
    die('SPL Autoload not available! Please use PHP > v5.1.2');
}

/**
 * Define application environment
 */
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV',
              (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV')
                                         : 'production'));

require '../../library/EngineBlock/ApplicationSingleton.php';

$application = EngineBlock_ApplicationSingleton::getInstance();
$application->bootstrap(APPLICATION_ENV);

$dispatcher = new EngineBlock_Dispatcher();
$dispatcher->setRouters(array(
    new EngineBlock_Router_Authorization(),
));
$dispatcher->dispatch();

$application->getHttpResponse()->send();
