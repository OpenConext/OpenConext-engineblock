<?php

use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/vendor/autoload.php';

// Validate required envs
if (!isset($_ENV['APP_ENV'])) {
    throw new RuntimeException('APP_ENV environment variable is not defined.');
}
if (!isset($_ENV['APP_SECRET']) || trim($_ENV['APP_SECRET']) === '') {
    throw new \RuntimeException('APP_SECRET is missing or empty. Set it in your environment configuration.');
}
if ($_ENV['APP_ENV'] === 'prod' && strlen($_ENV['APP_SECRET']) < 32) {
    throw new \RuntimeException('APP_SECRET must be at least 32 characters long in production.');
}
$debug = filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN);

if ($debug) {
    umask(0000);

    Debug::enable();
}

if ($trustedProxies = $_ENV['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_ENV['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts(explode(',', $trustedHosts));
}

$kernel = new Kernel($_ENV['APP_ENV'], $debug);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
