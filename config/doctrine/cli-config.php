<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;

require __DIR__ . '/../../library/EngineBlock/ApplicationSingleton.php';

$application = EngineBlock_ApplicationSingleton::getInstance();
$application->bootstrap();

$entityManager = $application->getDiContainer()->getEntityManager();

return ConsoleRunner::createHelperSet($entityManager);
