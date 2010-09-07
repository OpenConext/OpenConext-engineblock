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
    new EngineBlock_Router_OpenSocial(),
    new EngineBlock_Router_Service(),
    new EngineBlock_Router_CatchAll('default', 'index', 'internal'),
));
$dispatcher->setUseErrorHandling(false);
$dispatcher->dispatch();

$application->getHttpResponse()->send();
