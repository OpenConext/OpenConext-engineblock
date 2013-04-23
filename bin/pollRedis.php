<?php
$rootDir = realpath(__DIR__ . '/../');
require $rootDir . '/library/EngineBlock/ApplicationSingleton.php';

$application = EngineBlock_ApplicationSingleton::getInstance();
$application->bootstrap();

$redisClient = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getRedisClient();

$worker = new EngineBlock_Job_Worker($redisClient);
$worker->registerQueue(new EngineBlock_Job_Queue_LoginTracking($redisClient));

$worker->run();
