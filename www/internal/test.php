<?php

ini_set('display_errors', true);
require '../../library/EngineBlock/ApplicationSingleton.php';
EngineBlock_ApplicationSingleton::getInstance()->bootstrap();

//$application = EngineBlock_ApplicationSingleton::getInstance();
//$application->bootstrap();
//$config = $application->getConfiguration();
//$grouperClient = Grouper_Client_Rest::createFromConfig($config);
//echo $grouperClient;


$userId = 'urn:collab:person:test.surfguest.nl:oharsta';
//echo sha1($userId);
//echo date("Y-m-d H:i:s", strtotime("1 week")), "</br>";
$deprovisionConfig = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->cron->deprovision;
$deprovisionTime = strtotime('-' . $deprovisionConfig->idleTime);
$firstWarningTime = strtotime($deprovisionConfig->firstWarningTime, $deprovisionTime);
$secondWarningTime = strtotime($deprovisionConfig->secondWarningTime, $deprovisionTime);
echo 'deprovision: ' . date("Y-m-d H:i:s", $deprovisionTime), "</br>";
echo 'second-warning: ' . date("Y-m-d H:i:s", $secondWarningTime ), "</br>";
echo 'first-warning: ' . date("Y-m-d H:i:s", $firstWarningTime), "</br>";

