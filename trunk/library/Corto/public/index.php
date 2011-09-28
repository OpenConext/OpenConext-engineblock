<?php
require '../library/Corto/ProxyServer.php';
$server = new Corto_ProxyServer();

$config = array();
require '../configs/config.inc.php';
$server->setConfigs($config);

#$hostedEntities = array();
#require '../configs/metadata.hosted.inc.php';

$remoteEntities = array();
require '../configs/metadata.remote.inc.php';
$server->setRemoteEntities($remoteEntities);
$server->setHostedEntities($remoteEntities);

require '../configs/attributes.inc.php';
$server->setAttributeMetadata($attributes);

$server->setTemplateSource(
    Corto_ProxyServer::TEMPLATE_SOURCE_FILESYSTEM,
    array(
        'FilePath' => dirname(__FILE__) . '/../templates/')
);


require '../library/Corto/Module/Bindings.php';
$server->setBindingsModule(new Corto_Module_Bindings($server));

require '../library/Corto/Module/DemoServices.php';
$server->setServicesModule(new Corto_Module_DemoServices($server));

$server->serveRequest($_SERVER['PATH_INFO']);