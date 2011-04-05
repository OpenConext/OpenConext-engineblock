<?php

require '../../library/EngineBlock/ApplicationSingleton.php';

$application = EngineBlock_ApplicationSingleton::getInstance();
$application->bootstrap();

$dispatcher = new EngineBlock_Dispatcher();
$dispatcher->setRouters(array(
    new EngineBlock_Router_Authentication(),
    new EngineBlock_Router_CatchAll('authentication', 'index', 'index'),
));
$dispatcher->dispatch();

$application->getHttpResponse()->send();
