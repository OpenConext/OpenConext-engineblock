<?php

define('TEST_RESOURCES_DIR', dirname(__FILE__) . '/resources');

require_once realpath(__DIR__) . '/../app/bootstrap.php.cache';
require_once realpath(__DIR__) . '/../app/AppKernel.php';

$kernel = new AppKernel('test', true);
$kernel->loadClassCache();
$kernel->boot();
