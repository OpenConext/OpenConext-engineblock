<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

ini_set('date.timezone', 'Europe/Amsterdam');

// Include composer autoloader, this intentionally included instead of required since CI system does not
// use composer and will fail on requiring a non-existent autoload file
$rootDir = realpath(__DIR__ . '/../');
require_once $rootDir . '/vendor/autoload.php';

$application = EngineBlock_ApplicationSingleton::getInstance();

$log = new Zend_Log();
$log->addWriter(new Zend_Log_Writer_Null());
$application->setLogInstance($log);
