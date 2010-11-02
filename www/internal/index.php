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
$application->bootstrap(ENGINEBLOCK_ENV);

$dispatcher = new EngineBlock_Dispatcher();
$dispatcher->setRouters(array(
    new EngineBlock_Router_OpenSocial(),
    new EngineBlock_Router_Service(),
    new EngineBlock_Router_CatchAll('default', 'index', 'internal'),
));
$dispatcher->setUseErrorHandling(false);
$dispatcher->dispatch();

$application->getHttpResponse()->send();
