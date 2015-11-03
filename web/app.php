<?php

use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';

// Enable APC for autoloading to improve performance.
$apcLoader = new ApcClassLoader(sha1('engineblock'), $loader);
$loader->unregister();
$apcLoader->register(true);

require_once __DIR__.'/../app/AppKernel.php';
require_once __DIR__.'/../app/AppCache.php';

$symfonyEnvironment = getenv('SYMFONY_ENV') ?: 'prod';
$request = Request::createFromGlobals();

$kernel = new AppKernel($symfonyEnvironment, false);
$kernel->loadClassCache();

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
