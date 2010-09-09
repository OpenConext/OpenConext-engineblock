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
    new EngineBlock_Router_Authentication(),
    new EngineBlock_Router_OpenSocial(), // @todo temporarily available only
    new EngineBlock_Router_CatchAll('authentication', 'index', 'index'),
));
$dispatcher->dispatch();

$application->getHttpResponse()->send();