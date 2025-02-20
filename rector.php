<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Symfony\Set\SymfonySetList;

return RectorConfig::configure()
    ->withSymfonyContainerXml(__DIR__ . '/app/cache/ci/appCiDebugProjectContainer.xml')
    ->withSets([
        SymfonySetList::SYMFONY_40,
        SymfonySetList::SYMFONY_41,
        SymfonySetList::SYMFONY_42,
        SymfonySetList::SYMFONY_43,
        SymfonySetList::SYMFONY_44,
    ])
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/languages',
        __DIR__ . '/library',
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/theme',
        __DIR__ . '/web',
    ])
    // uncomment to reach your current PHP version
    // ->withPhpSets()
    ->withTypeCoverageLevel(0);

