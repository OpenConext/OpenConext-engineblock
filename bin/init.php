<?php
// Used by rescue, this will initialized the autoloading and the instance will be used by jobs also
require_once $rootDir . '/library/EngineBlock/ApplicationSingleton.php';
$application = EngineBlock_ApplicationSingleton::getInstance();
$application->bootstrap();