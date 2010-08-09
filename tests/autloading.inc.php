<?php

require dirname(__FILE__).'/../library/EngineBlock/ApplicationSingleton.php';

$application = EngineBlock_ApplicationSingleton::getInstance();
spl_autoload_register(array($application, 'autoLoad'));