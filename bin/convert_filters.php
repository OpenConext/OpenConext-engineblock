<?php

require '../library/EngineBlock/AttributeMapper/Abstract.php';
require '../library/EngineBlock/AttributeMapper/Oid2Urn.php';
require '../library/EngineBlock/AttributeMapper/Urn2Oid.php';


$attributes = json_decode(file_get_contents('../application/configs/attributes.json'));
$oldAttributes = $attributes;
foreach (EngineBlock_AttributeMapper_Urn2Oid::$mapping as $attributeName => $oidVariant) {
    if (isset($attributes->$oidVariant)) {
        if ($attributes->$oidVariant !== $attributeName) {
            var_dump($attributes->$attributeName, $attributeName, '!==', $oidVariant);
        }
    }
    else {
        $attributes->$oidVariant = $attributeName;
    }
    if (!isset($attributes->$attributeName)) {
        $attributes->$attributeName = new stdClass();
    }
}

foreach (EngineBlock_AttributeMapper_Oid2Urn::$mapping as $oidVariant => $attributeName) {
    if (isset($attributes->$oidVariant)) {
        if ($attributes->$oidVariant !== $attributeName) {
            var_dump($attributes->$attributeName, $attributeName, "!==", $oidVariant);
        }
    }
    else {
        $attributes->$oidVariant = $attributeName;
    }
    if (!isset($attributes->$attributeName)) {
        $attributes->$attributeName = new stdClass();
    }
}

echo json_encode($attributes, JSON_PRETTY_PRINT) . PHP_EOL;