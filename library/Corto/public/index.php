<?php

require '../library/Corto/ProxyServer.php';
$server = new Corto_ProxyServer();

$config = array();
require '../configs/config.php';
$server->setConfiguration($config);

$entities = array();
require '../configs/metadata.hosted.inc.php';
$server->setHostedEntities($entities);

$entities = array();
require '../configs/metadata.remote.inc.php';
$server->setRemoteEntities($entities);

$certificates = array();
require '../configs/certificates.inc.php';
$server->setCertificates($certificates);

$server->setTemplateSource('filesystem', array('path'=>dirname(__FILE__) . '/../templates/'));

require '../library/Corto/Module/Abstract.php';
require '../library/Corto/Module/Bindings.php';
require '../library/Corto/Module/Encryption.php';
require '../library/Corto/Module/Services.php';
require '../library/Corto/Module/Signing.php';

$server->setBindingModule(new Corto_Module_Bindings($server))
    ->setEncryptionModule(new Corto_Module_Encryption($server))
    ->setServicesModule(new Corto_Module_Services($server))
    ->setSigningModule(new Corto_Module_Signing($server));

$server->serveRequest();