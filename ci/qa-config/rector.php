<?php

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/../../config',
        __DIR__ . '/../../src',
        __DIR__ . '/../../library',
        __DIR__ . '/../../tests',
    ])
    ->withPhpSets()
    ->withComposerBased(doctrine: true, phpunit: true, symfony: true)
    ->withAttributesSets(symfony: false, doctrine: true, phpunit: true)
    ->withSkip([
        \Rector\Php53\Rector\Ternary\TernaryToElvisRector::class,
        \Rector\Php54\Rector\Array_\LongArrayToShortArrayRector::class,
        \Rector\Php70\Rector\StmtsAwareInterface\IfIssetToCoalescingRector::class,
        \Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector::class,
        \Rector\Php71\Rector\List_\ListToArrayDestructRector::class,
        \Rector\Php73\Rector\FuncCall\StringifyStrNeedlesRector::class,
        \Rector\Php73\Rector\FuncCall\SetCookieRector::class,
        \Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector::class,
        \Rector\Php74\Rector\Assign\NullCoalescingOperatorRector::class,
        \Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector::class,
        \Rector\Php80\Rector\Class_\StringableForToStringRector::class,
        \Rector\Php80\Rector\Identical\StrStartsWithRector::class,
        \Rector\Php80\Rector\NotIdentical\StrContainsRector::class,
        \Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector::class,
        \Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector::class,
        \Rector\Php80\Rector\FuncCall\ClassOnObjectRector::class,
        \Rector\Php81\Rector\Array_\FirstClassCallableRector::class,
        \Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector::class,
        \Rector\Php81\Rector\Property\ReadOnlyPropertyRector::class,
        \Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector::class,
    ]);
