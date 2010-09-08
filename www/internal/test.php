<?php

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
    new EngineBlock_Router_Default(),
));
$dispatcher->dispatch($_GET['uri']);

$application->getHttpResponse()->send();