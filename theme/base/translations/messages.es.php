<?php

$overrides = [];
$overridesFile = __DIR__ . '/overrides.es.php';
if (file_exists($overridesFile)) {
    $overrides = require $overridesFile;
}

return $overrides + [
];
