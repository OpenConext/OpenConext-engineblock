<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use App\Kernel;

define('TEST_RESOURCES_DIR', __DIR__ . '/resources');

require_once realpath(__DIR__) . '/../vendor/autoload.php';

$worktreeRoot   = (string) realpath(__DIR__ . '/..');
$vendorRealPath = (string) realpath(__DIR__ . '/../vendor');
if (!str_starts_with($vendorRealPath, $worktreeRoot . DIRECTORY_SEPARATOR)) {
    $worktreeLibrary = $worktreeRoot . '/library';
    $worktreeSrc     = $worktreeRoot . '/src';
    spl_autoload_register(static function (string $class) use ($worktreeLibrary, $worktreeSrc): bool {
        $psr0File = $worktreeLibrary . '/' . str_replace('_', '/', $class) . '.php';
        if (file_exists($psr0File)) {
            require $psr0File;
            return true;
        }
        if (str_starts_with($class, 'OpenConext\\')) {
            $relative = substr($class, strlen('OpenConext\\'));
            $psr4File = $worktreeSrc . '/OpenConext/' . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($psr4File)) {
                require $psr4File;
                return true;
            }
        }
        return false;
    }, true, true);
}

require_once realpath(__DIR__) . '/../src/Kernel.php';

$kernel = new Kernel('test', true);
$kernel->boot();
