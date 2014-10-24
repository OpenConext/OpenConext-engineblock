#!/usr/bin/env php
<?php

require realpath(__DIR__ . '/../vendor') . '/autoload.php';

$application = new EngineBlock_DbPatch_Core_Application();
$application->main();