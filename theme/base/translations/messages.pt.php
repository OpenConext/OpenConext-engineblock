<?php

$overrides = [];
$overridesFile = __DIR__ . '/overrides.pt.php';
if (file_exists($overridesFile)) {
    $overrides = require $overridesFile;
}

return $overrides + [
];
