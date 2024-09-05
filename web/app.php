<?php

use Symfony\Component\HttpFoundation\Request;

$loader = require __DIR__.'/../app/autoload.php';
require_once __DIR__.'/../app/AppKernel.php';

$symfonyEnvironment = getenv('SYMFONY_ENV') ?: 'dev';

$request = Request::createFromGlobals();
$kernel = new AppKernel($symfonyEnvironment, false);
$kernel->boot();

$trustedProxies = $kernel->getContainer()->getParameter('trusted_proxies');
//Request::setTrustedProxies($trustedProxies, Request::HEADER_X_FORWARDED_ALL);

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
