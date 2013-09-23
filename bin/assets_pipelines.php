#!/usr/bin/env php
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

require realpath(__DIR__ . '/../vendor') . '/autoload.php';

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Filter\JSMinPlusFilter;

$js = new AssetCollection(
    array(
        new FileAsset('../www/authentication/javascript/respond.min.js'),
        new FileAsset('../www/authentication/javascript/jquery-1.10.2.min.js'),
        new FileAsset('../www/authentication/javascript/jquery.tmpl.min.js'),
        new FileAsset('../www/authentication/javascript/jquery.cookie.js'),
        new FileAsset('../www/authentication/javascript/matchMedia.js'),
        new FileAsset('../www/authentication/javascript/discover.js'),
    ), array(
    new JSMinPlusFilter(),

));

// the code is merged when the asset is dumped
$dir = '../www/authentication/javascript/generated/' . time();
mkdir($dir, 0777, true);
file_put_contents($dir . '/js.min.js', $js->dump());

