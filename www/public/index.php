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
    new EngineBlock_Router_Authentication(),
    new EngineBlock_Router_CatchAll('authentication', 'index', 'index'),
));
$dispatcher->dispatch();

$application->getHttpResponse()->send();
