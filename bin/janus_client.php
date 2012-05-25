<?php

if (PHP_SAPI !== 'cli') {
    die('Command line access only' . PHP_EOL);
}

if (!isset($argv[1])) {
    die("Please supply a method to call, like so: php janus_client.php getMetadata https://example.edu" . PHP_EOL);
}

require './../library/EngineBlock/ApplicationSingleton.php';

$application = EngineBlock_ApplicationSingleton::getInstance();
$application->bootstrap();

$config = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->serviceRegistry;
$restClient = new Janus_Rest_Client($config->location, $config->user, $config->user_secret);

$client = new Janus_Client();
$client->setRestClient($restClient);
$methodName = $argv[1];
$arguments = array_slice($argv, 2);
$result = call_user_func_array(array($client, $methodName), $arguments);

var_dump($restClient->getHttpClient()->getLastRequest());
var_dump($restClient->getHttpClient()->getLastResponse()->getHeadersAsString());
var_dump($restClient->getHttpClient()->getLastResponse()->getBody());
var_dump($result);
