<?php

ini_set('display_errors', true);
error_reporting(E_ALL);

if(!function_exists('spl_autoload_register')) {
    die('SPL Autoload not available! Please use PHP > v5.1.2');
}

require '../library/EngineBlock/ApplicationSingleton.php';


$application = EngineBlock_ApplicationSingleton::getInstance();
$application->bootstrap();

EngineBlock_Dispatcher::dispatch();

$application->getHttpResponse()->send();