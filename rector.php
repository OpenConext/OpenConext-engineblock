<?php

declare(strict_types=1);

/**
 * @TODO remove post SF upgrade
 */

use Rector\Config\RectorConfig;
use Rector\Symfony\Bridge\Symfony\Routing\SymfonyRoutesProvider;
use Rector\Symfony\Contract\Bridge\Symfony\Routing\SymfonyRoutesProviderInterface;
use Rector\Symfony\Set\TwigSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/languages',
        __DIR__ . '/library',
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/theme',
        __DIR__ . '/web',
    ])
    ->withSkip([
        __DIR__ . '/app/cache'
    ])
    ->withSymfonyContainerXml(__DIR__. '/app/cache/ci/appCiDebugProjectContainer.xml')
    ->withSymfonyContainerPhp(__DIR__.'/tests/symfony-container.php')
    ->registerService(SymfonyRoutesProvider::class, SymfonyRoutesProviderInterface::class)
    // uncomment to reach your current PHP version
    // ->withPhpSets()

    ->withSets([TwigSetList::TWIG_UNDERSCORE_TO_NAMESPACE])
//    ->withSets([SymfonySetList::SYMFONY_34])
//    ->withSets([SymfonySetList::SYMFONY_40])
//    ->withSets([SymfonySetList::SYMFONY_41])
//    ->withSets([SymfonySetList::SYMFONY_42])
//    ->withSets([SymfonySetList::SYMFONY_43])
//    ->withSets([SymfonySetList::SYMFONY_44])

    ->withTypeCoverageLevel(0);
