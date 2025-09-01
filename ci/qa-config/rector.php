<?php

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/library',
    ])
    ->withSets([
        LevelSetList::UP_TO_PHP_82,
    ])
    ->withSkip([
        \Rector\Php53\Rector\Ternary\TernaryToElvisRector::class,
        \Rector\Php54\Rector\Array_\LongArrayToShortArrayRector::class,
        \Rector\Php70\Rector\StmtsAwareInterface\IfIssetToCoalescingRector::class,
        \Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector::class,
        \Rector\Php71\Rector\List_\ListToArrayDestructRector::class,
        \Rector\Php73\Rector\FuncCall\StringifyStrNeedlesRector::class,
        \Rector\Php73\Rector\FuncCall\SetCookieRector::class,
        \Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector::class,
        \Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector::class,
        \Rector\Php80\Rector\Class_\StringableForToStringRector::class,
        \Rector\Php80\Rector\Identical\StrStartsWithRector::class,
        \Rector\Php80\Rector\NotIdentical\StrContainsRector::class,
        \Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector::class,
        \Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector::class,
        \Rector\Php80\Rector\FunctionLike\MixedTypeRector::class,
        \Rector\Php80\Rector\FuncCall\ClassOnObjectRector::class,
    ])
    ;
