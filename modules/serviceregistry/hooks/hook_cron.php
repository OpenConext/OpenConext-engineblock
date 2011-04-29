<?php

define('SERVICEREGISTRY_DEFAULT_VALID_UNTIL', '+1 year');
define('SERVICEREGISTRY_DEFAULT_CACHE_UNTIL', '+1 day');

require __DIR__ . '/../lib/EntityController.php';

/**
 * Cron hook for SURFconext Service Registry
 *
 * This hook downloads the metadata of the entities registered in JANUS and
 * update the entities with the new metadata.
 *
 * @param array &$cron_info The array with the tags and output summary of the cron run
 *
 * @return void
 *
 * @since Function available since Release 1.4.0
 */
function serviceregistry_hook_cron(&$cron_info) {
    assert('is_array($croninfo)');
    assert('array_key_exists("summary", $croninfo)');
    assert('array_key_exists("tag", $croninfo)');

    SimpleSAML_Logger::info('cron [janus]: Running cron in cron tag [' . $cron_info['tag'] . '] ');

    $hasErrors = false;

    try {
        $janus_config           = SimpleSAML_Configuration::getConfig('module_janus.php');
        $serviceregistry_config = SimpleSAML_Configuration::getConfig('module_serviceregistry.php');

        $cron_tags = $serviceregistry_config->getArray('cron_tags', array());

        if (!in_array($cron_info['tag'], $cron_tags)) {
            return; // Nothing to do: it's not our time
        }

        $util = new sspmod_janus_AdminUtil();
        $entities = $util->getEntities();

        foreach ($entities as $partial_entity) {
            $entity_controller = new sspmod_serviceregistry_EntityController($janus_config);

            $eid = $partial_entity['eid'];
            if(!$entity_controller->setEntity($eid)) {
                $hasErrors = true;
                $cron_info['summary'][] = "[Error][$eid] Failed import of entity. Wrong eid '$eid'.";
                continue;
            }

            $entity_controller->loadEntity();
            $entity = $entity_controller->getEntity();
            $entity_id = $entity->getEntityId();
            $metadata_url = $entity->getMetadataURL();
            $metadata_caching_info = $entity_controller->getMetadataCaching();

            if (empty($metadata_url)) {
                $cron_info['summary'][] = "[Warning][$entity_id] No metadata url. ";
                continue;
            }
            
            $nextRun = time();
            switch ($cron_info['tag']) {
                case 'hourly':
                    $nextRun += 3600;
                    break;
                case 'daily':
                    $nextRun += 24 * 60 * 60;
                    break;
                case 'frequent':
                    $nextRun += 0; // How often is frequent?
                    break;
                default:
                    throw new Exception("Unknown cron tag '{$cron_info['tag']}'");
            }

            if ($metadata_caching_info['validUntil'] > $nextRun && $metadata_caching_info['cacheUntil'] > $nextRun) {
                $cron_info['summary'][] = "[Notice][$entity_id] Should not update, cache still valid.";
                continue;
            }

            $xml = file_get_contents($metadata_url);
            if (!$xml) {
                $hasErrors = true;
                $cron_info['summary'][] = "[Error][$entity_id] Failed import of entity. Bad URL '$metadata_url'? ";
                continue;
            }

            $updated = false;

            if($entity->getType() == 'saml20-sp') {
                $status_code = $entity_controller->importMetadata20SP($xml, $updated);
                if ($status_code !== 'status_metadata_parsed_ok') {
                    $hasErrors = true;
                    $cron_info['summary'][] = "[Error][$entity_id] Entity not updated";
                }
            } else if($entity->getType() == 'saml20-idp') {
                $status_code = $entity_controller->importMetadata20IdP($xml, $updated);
                if ($status_code !== 'status_metadata_parsed_ok') {
                    $hasErrors = true;
                    $cron_info['summary'][] = "[Error][$entity_id] Entity not updated";
                }
            }
            else {
                $cron_info['summary'][] = "[Error][$entity_id] Failed import of entity. Wrong type";
            }

            if ($updated) {
                $entity->setParent($entity->getRevisionid());
                $entity_controller->saveEntity();

                $cron_info['summary'][] = "[Notice][$entity_id] Entity updated";

                $metadata_caching_info = _serviceregistry_hook_cron_getMetaDataCachingInfo($xml, $entity_id);
                $entity_controller->setMetadataCaching(
                    $metadata_caching_info['validUntil'],
                    $metadata_caching_info['cacheUntil']
                );
            }
            else {
                $cron_info['summary'][] = "[Notice][$entity_id] Entity not updated, no changes required";

                // Update metadata caching info (validUntil )
                $metadata_caching_info = _serviceregistry_hook_cron_getMetaDataCachingInfo($xml, $entity_id);
                $entity_controller->setMetadataCaching(
                    $metadata_caching_info['validUntil'],
                    $metadata_caching_info['cacheUntil']
                );
            }
        }

    } catch (Exception $e) {
        $hasErrors = true;
        $cron_info['summary'][] = '[Error] ' . $e->getMessage();
    }
    
    if ($hasErrors) {
        _serviceregistry_hook_cron_mailTechnicalContact($cron_info['tag'], $cron_info['summary']);
    }
}

function _serviceregistry_hook_cron_mailTechnicalContact($tag, $summary)
{
    $config = SimpleSAML_Configuration::getInstance();
    $time = date(DATE_RFC822);
    $url = SimpleSAML_Utilities::selfURL();
    $message = '<h1>Cron report</h1><p>Cron ran at ' . $time . '</p>' .
        '<p>URL: <tt>' . $url . '</tt></p>' .
        '<p>Tag: ' . $tag . "</p>\n\n" .
        '<ul><li>' . join('</li><li>', $summary) . '</li></ul>';

    $toAddress = $config->getString('technicalcontact_email', 'na@example.org');
    if ($toAddress == 'na@example.org') {
        SimpleSAML_Logger::error('Cron - Could not send email. [technicalcontact_email] not set in config.');
    } else {
        $email = new SimpleSAML_XHTML_EMail($toAddress, 'ServiceRegistry cron report', 'coin-beheer@surfnet.nl');
        $email->setBody($message);
        $email->send();
    }
}

function _serviceregistry_hook_cron_getMetaDataCachingInfo($xml, $entity_id)
{
    $document = new DOMDocument();
    $document->loadXML($xml);

    $query = new DOMXPath($document);
    $query->registerNamespace('md', "urn:oasis:names:tc:SAML:2.0:metadata");

    $entitiesCacheDuration  = $query->query('/md:EntitiesDescriptor/@cacheDuration');
    $entitiesValidUntil     = $query->query('/md:EntitiesDescriptor/@validUntil');
    $entityCacheDuration    = $query->query("//md:EntityDescriptor[entityID=$entity_id]/@cacheDuration");
    $entityValidUntil       = $query->query("//md:EntityDescriptor[entityID=$entity_id]/@validUntil");
    $spCacheDuration        = $query->query("//md:EntityDescriptor[entityID=$entity_id]/md:SPSSODescriptor/@cacheDuration");
    $spValidUntil           = $query->query("//md:EntityDescriptor[entityID=$entity_id]/md:SPSSODescriptor/@validUntil");
    $idpCacheDuration       = $query->query("//md:EntityDescriptor[entityID=$entity_id]/md:IDPSSODescriptor/@cacheDuration");
    $idpValidUntil          = $query->query("//md:EntityDescriptor[entityID=$entity_id]/md:IDPSSODescriptor/@validUntil");

    $defaultValidUntil = strtotime(SERVICEREGISTRY_DEFAULT_VALID_UNTIL);
    $validUntil = _serviceregistry_hook_cron_getEarliestDateFromXml($defaultValidUntil, $entitiesValidUntil);
    $validUntil = _serviceregistry_hook_cron_getEarliestDateFromXml($validUntil, $entityValidUntil);
    $validUntil = _serviceregistry_hook_cron_getEarliestDateFromXml($validUntil, $spValidUntil);
    $validUntil = _serviceregistry_hook_cron_getEarliestDateFromXml($validUntil, $idpValidUntil);

    // @todo parse cacheDurations with lib/Xml/Duration.php first

    $defaultCacheDuration = strtotime(SERVICEREGISTRY_DEFAULT_CACHE_UNTIL);
    $cacheDuration = _serviceregistry_hook_cron_getEarliestDateFromXml($defaultCacheDuration, $entitiesCacheDuration);
    $cacheDuration = _serviceregistry_hook_cron_getEarliestDateFromXml($cacheDuration, $entityCacheDuration);
    $cacheDuration = _serviceregistry_hook_cron_getEarliestDateFromXml($cacheDuration, $spCacheDuration);
    $cacheDuration = _serviceregistry_hook_cron_getEarliestDateFromXml($cacheDuration, $idpCacheDuration);

    return array(
        'validUntil'    => $validUntil,
        'cacheUntil' => $cacheDuration,
    );
}

function _serviceregistry_hook_cron_getEarliestDateFromXml($validUntil, $xmlValidUntil)
{
    if (!$xmlValidUntil || $xmlValidUntil->length === 0) {
        return $validUntil;
    }

    $xmlValidUntil = strtotime($xmlValidUntil->item(0)->nodeValue);
    if ($xmlValidUntil < $validUntil) {
        $validUntil = $xmlValidUntil;
    }
    return $validUntil;
}