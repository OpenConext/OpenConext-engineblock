<?php
/**
 * SURFconext Service Registry
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
 * @category  SURFconext Service Registry
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

ini_set('display_errors', true);
require __DIR__ . '/../www/_includes.php';
require __DIR__ . '/../lib/ServiceRegistry/Cron/Job/MetadataRefresh.php';
require __DIR__ . '/../lib/ServiceRegistry/Cron/Logger.php';
require __DIR__ . '/../lib/ServiceRegistry/Cron/Job/ValidateEntityCertificate.php';
require __DIR__ . '/../lib/ServiceRegistry/Cron/Job/ValidateEntityEndpoints.php';

/**
 * Cron hook for SURFconext Service Registry
 *
 * This hook downloads the metadata of the entities registered in JANUS and
 * update the entities with the new metadata.
 *
 * @param array &$cronInfo The array with the tags and output summary of the cron run
 *
 * @return void
 *
 * @since Function available since Release 1.4.0
 */
function serviceregistry_hook_cron(&$cronInfo) {
    assert('is_array($cronInfo)');
    assert('array_key_exists("summary", $cronInfo)');
    assert('array_key_exists("tag", $cronInfo)');

    SimpleSAML_Logger::info('cron [janus]: Running cron in cron tag [' . $cronInfo['tag'] . '] ');

    // Refresh metadata
    $refresher = new ServiceRegistry_Cron_Job_MetadataRefresh();
    $summaryLines = $refresher->runForCronTag($cronInfo['tag']);
    $cronInfo['summary'] = array_merge($cronInfo['summary'], $summaryLines);

    // Validate entity signing certificates
    $validator = new ServiceRegistry_Cron_Job_ValidateEntityCertificate();
    $summaryLines = $validator->runForCronTag($cronInfo['tag']);
    $cronInfo['summary'] = array_merge($cronInfo['summary'], $summaryLines);

    // Validate entity endpoints
    $validator = new ServiceRegistry_Cron_Job_ValidateEntityEndpoints();
    $summaryLines = $validator->runForCronTag($cronInfo['tag']);
    $cronInfo['summary'] = array_merge($cronInfo['summary'], $summaryLines);
}