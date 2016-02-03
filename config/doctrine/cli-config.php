<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once __DIR__ . '/../../app/bootstrap.php.cache';
require_once __DIR__ . '/../../app/AppKernel.php';

$symfonyEnvironment = getenv('SYMFONY_ENV') ?: 'prod';
$kernel             = new AppKernel($symfonyEnvironment, false);
$kernel->boot();

$application = EngineBlock_ApplicationSingleton::getInstance();

$entityManager = $application->getDiContainer()->getEntityManager();

return ConsoleRunner::createHelperSet($entityManager);
