<?php

require __DIR__.'/vendor/autoload.php';

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

// Load environment variables from .env file
if (file_exists(__DIR__.'/.env')) {
    (new Dotenv())->load(__DIR__.'/.env');
}

// Set environment variables if not already set
$_SERVER['APP_ENV'] = $_SERVER['APP_ENV'] ?? 'dev';
$_SERVER['APP_DEBUG'] = $_SERVER['APP_DEBUG'] ?? '1';

// Try to instantiate the kernel
try {
    $kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
    echo "Successfully instantiated Symfony 4 Kernel!\n";
    echo "Environment: " . $kernel->getEnvironment() . "\n";
    echo "Debug: " . ($kernel->isDebug() ? 'true' : 'false') . "\n";
    echo "Project Dir: " . $kernel->getProjectDir() . "\n";
} catch (\Exception $e) {
    echo "Error instantiating Symfony 4 Kernel: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
