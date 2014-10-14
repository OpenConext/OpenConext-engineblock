<?php

require '../../library/EngineBlock/ApplicationSingleton.php';

$application = EngineBlock_ApplicationSingleton::getInstance();
$application->bootstrap();

$dispatcher = new EngineBlock_Dispatcher();

$dispatcher->setRouters(array(
    EngineBlock_Router_Authentication::create()->setDefaultModuleName('Authentication'),
));
$dispatcher->dispatch();

$application->getHttpResponse()->send();
