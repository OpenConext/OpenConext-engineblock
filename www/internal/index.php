<?php

require '../../library/EngineBlock/ApplicationSingleton.php';
ini_set('display_errors', true);
$application = EngineBlock_ApplicationSingleton::getInstance();
$application->bootstrap();

$dispatcher = new EngineBlock_Dispatcher();
$dispatcher->setRouters(array(
    new EngineBlock_Router_OpenSocial(),
    new EngineBlock_Router_Service(),
    new EngineBlock_Router_CatchAll('default', 'index', 'internal'),
));
$dispatcher->setUseErrorHandling(false);
$dispatcher->dispatch();

$application->getHttpResponse()->send();
