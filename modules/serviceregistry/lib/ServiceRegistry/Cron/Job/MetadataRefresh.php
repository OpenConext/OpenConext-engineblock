<?php
/**
 *
 */

define('SERVICEREGISTRY_DEFAULT_VALID_UNTIL', '+1 year');
define('SERVICEREGISTRY_DEFAULT_CACHE_UNTIL', '+1 day');

/**
 *
 */ 
class ServiceRegistry_Cron_Job_MetadataRefresh
{
    const CONFIG_WITH_TAGS_TO_RUN_ON = 'metadata_refresh_cron_tags';

    public function __construct()
    {
    }

    public function runForCronTag($cronTag)
    {
        if (!$this->_isExecuteRequired($cronTag)) {
            return array();
        }

        $cronLogger = new ServiceRegistry_Cron_Logger();
        try {
            $janusConfig = SimpleSAML_Configuration::getConfig('module_janus.php');

            $util = new sspmod_janus_AdminUtil();
            $entities = $util->getEntities();

            foreach ($entities as $partialEntity) {
                $entityController = new sspmod_serviceregistry_EntityController($janusConfig);

                $eid = $partialEntity['eid'];
                if(!$entityController->setEntity($eid)) {
                    $cronLogger->with($eid)->error(
                        "Failed import of entity. Wrong eid '$eid'."
                    );
                    continue;
                }

                $entityController->loadEntity();
                $entity = $entityController->getEntity();
                $entityId = $entity->getEntityId();
                $metadataUrl = $entity->getMetadataURL();
                $metadataCachingInfo = $entityController->getMetadataCaching();

                if (empty($metadataUrl)) {
                    $cronLogger->with($entityId)->warn(
                        "No metadata url."
                    );
                    continue;
                }

                $nextRun = time();
                switch ($cronTag) {
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
                        throw new Exception("Unknown cron tag '{$cronTag}'");
                }

                if ($metadataCachingInfo['validUntil'] > $nextRun && $metadataCachingInfo['cacheUntil'] > $nextRun) {
                    $cronLogger->with($entityId)->notice(
                        "Should not update, cache still valid."
                    );
                    continue;
                }

                $xml = file_get_contents($metadataUrl);
                if (!$xml) {
                    $cronLogger->with($entityId)->error(
                        "Failed import of entity. Bad URL '$metadataUrl'? "
                    );
                    continue;
                }

                $updated = false;

                if($entity->getType() == 'saml20-sp') {
                    $statusCode = $entityController->importMetadata20SP($xml, $updated);
                    if ($statusCode !== 'status_metadata_parsed_ok') {
                        $cronLogger->with($entityId)->error(
                            "Entity not updated"
                        );
                    }
                } else if($entity->getType() == 'saml20-idp') {
                    $statusCode = $entityController->importMetadata20IdP($xml, $updated);
                    if ($statusCode !== 'status_metadata_parsed_ok') {
                        $cronLogger->with($entityId)->error(
                            "Entity not updated"
                        );
                    }
                }
                else {
                    $cronLogger->with($entityId)->error(
                        "Failed import of entity. Wrong type"
                    );
                }

                if ($updated) {
                    $entity->setParent($entity->getRevisionid());
                    $entityController->saveEntity();

                    $cronLogger->with($entityId)->notice(
                        "Entity updated"
                    );

                    $metadataCachingInfo = $this->_getMetaDataCachingInfo($xml, $entityId);
                    $entityController->setMetadataCaching(
                        $metadataCachingInfo['validUntil'],
                        $metadataCachingInfo['cacheUntil']
                    );
                }
                else {
                    $cronLogger->with($entityId)->notice(
                        "Entity not updated, no changes required"
                    );

                    // Update metadata caching info (validUntil )
                    $metadataCachingInfo = $this->_getMetaDataCachingInfo($xml, $entityId);
                    $entityController->setMetadataCaching(
                        $metadataCachingInfo['validUntil'],
                        $metadataCachingInfo['cacheUntil']
                    );
                }
            }

        } catch (Exception $e) {
            $cronLogger->error($e->getMessage());
        }

        $summaryLines = $cronLogger->getSummaryLines();
        if ($cronLogger->hasErrors()) {
            $this->_mailTechnicalContact($cronTag, $summaryLines);
        }
        return $summaryLines;
    }

    protected function _isExecuteRequired($cronTag)
    {
        $serviceRegistryConfig = SimpleSAML_Configuration::getConfig('module_serviceregistry.php');

        $cronTags = $serviceRegistryConfig->getArray(self::CONFIG_WITH_TAGS_TO_RUN_ON, array());

        if (!in_array($cronTag, $cronTags)) {
            return false; // Nothing to do: it's not our time
        }
        return true;
    }

    protected function _mailTechnicalContact($tag, $summary)
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

    protected function _getMetaDataCachingInfo($xml, $entityId)
    {
        $document = new DOMDocument();
        $document->loadXML($xml);

        $query = new DOMXPath($document);
        $query->registerNamespace('md', "urn:oasis:names:tc:SAML:2.0:metadata");

        $entitiesCacheDuration  = $query->query('/md:EntitiesDescriptor/@cacheDuration');
        $entitiesValidUntil     = $query->query('/md:EntitiesDescriptor/@validUntil');
        $entityCacheDuration    = $query->query("//md:EntityDescriptor[entityID=$entityId]/@cacheDuration");
        $entityValidUntil       = $query->query("//md:EntityDescriptor[entityID=$entityId]/@validUntil");
        $spCacheDuration        = $query->query("//md:EntityDescriptor[entityID=$entityId]/md:SPSSODescriptor/@cacheDuration");
        $spValidUntil           = $query->query("//md:EntityDescriptor[entityID=$entityId]/md:SPSSODescriptor/@validUntil");
        $idpCacheDuration       = $query->query("//md:EntityDescriptor[entityID=$entityId]/md:IDPSSODescriptor/@cacheDuration");
        $idpValidUntil          = $query->query("//md:EntityDescriptor[entityID=$entityId]/md:IDPSSODescriptor/@validUntil");

        $defaultValidUntil = strtotime(SERVICEREGISTRY_DEFAULT_VALID_UNTIL);
        $validUntil = $this->_getEarliestDateFromXml($defaultValidUntil, $entitiesValidUntil);
        $validUntil = $this->_getEarliestDateFromXml($validUntil, $entityValidUntil);
        $validUntil = $this->_getEarliestDateFromXml($validUntil, $spValidUntil);
        $validUntil = $this->_getEarliestDateFromXml($validUntil, $idpValidUntil);

        // @todo parse cacheDurations with lib/Xml/Duration.php first

        $defaultCacheDuration = strtotime(SERVICEREGISTRY_DEFAULT_CACHE_UNTIL);
        $cacheDuration = $this->_getEarliestDateFromXml($defaultCacheDuration, $entitiesCacheDuration);
        $cacheDuration = $this->_getEarliestDateFromXml($cacheDuration, $entityCacheDuration);
        $cacheDuration = $this->_getEarliestDateFromXml($cacheDuration, $spCacheDuration);
        $cacheDuration = $this->_getEarliestDateFromXml($cacheDuration, $idpCacheDuration);

        return array(
            'validUntil'    => $validUntil,
            'cacheUntil' => $cacheDuration,
        );
    }

    protected function _getEarliestDateFromXml($validUntil, $xmlValidUntil)
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
}
