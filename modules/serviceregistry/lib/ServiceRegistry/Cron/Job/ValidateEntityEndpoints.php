<?php
/**
 *
 */

/**
 *
 */ 
class ServiceRegistry_Cron_Job_ValidateEntityEndpoints
{
    const CONFIG_WITH_TAGS_TO_RUN_ON = 'validate_entity_endpoints_cron_tags';

    protected $_endpointMetadataFields = array(
        'SingleSignOnService',
        'AssertionConsumerService',
        'SingleLogoutService'
    );

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
            $srConfig = SimpleSAML_Configuration::getConfig('module_serviceregistry.php');
            $rootCertificatesFile = $srConfig->getString('ca_bundle_file');

            $util = new sspmod_janus_AdminUtil();
            $entities = $util->getEntities();

            foreach ($entities as $partialEntity) {
                $entityController = new sspmod_serviceregistry_EntityController($janusConfig);

                $eid = $partialEntity['eid'];
                if(!$entityController->setEntity($eid)) {
                    $cronLogger->with($eid)->error("Failed import of entity. Wrong eid '$eid'.");
                    continue;
                }

                $entityController->loadEntity();
                $entityId = $entityController->getEntity()->getEntityid();
                $entityMetadata = $entityController->getMetaArray();

                foreach ($this->_endpointMetadataFields as $endPointMetaKey) {
                    if (!isset($entityMetadata[$endPointMetaKey])) {
                        // This entity does not have this binding
                        continue;
                    }

                    foreach ($entityMetadata[$endPointMetaKey] as $index => $binding) {
                        $key = $endPointMetaKey . ':' .$index;
                        if (!isset($binding['Location']) || trim($binding['Location'])==="") {
                            $cronLogger->with($entityId)->with($key)->error(
                                "Binding has no Location?"
                            );
                            continue;
                        }

                        try {
                            $sslUrl = new OpenSsl_Url($binding['Location']);
                        }
                        catch (Exception $e) {
                            $cronLogger->with($entityId)->with($key)->with($sslUrl->getUrl())->error(
                                "Endpoint is not a valid URL"
                            );
                            continue;
                        }

                        if (!$sslUrl->isHttps()) {
                            $cronLogger->with($entityId)->with($key)->with($sslUrl->getUrl())->error(
                                "Endpoint is not HTTPS"
                            );
                            continue;
                        }


                        $connectSuccess = $sslUrl->connect();
                        if (!$connectSuccess) {
                            $cronLogger->with($entityId)->with($key)->with($sslUrl->getUrl())->error(
                                "Endpoint is unreachable"
                            );
                            continue;
                        }


                        if (!$sslUrl->isCertificateValidForUrlHostname()) {
                            $urlHostName = $sslUrl->getHostName();
                            $validHostNames = $sslUrl->getServerCertificate()->getValidHostNames();
                            $cronLogger->with($entityId)->with($key)->with($sslUrl->getUrl())->error(
                                "Certificate does not match the hostname '$urlHostName' (instead it matches " .
                                implode(', ', $validHostNames) .
                                ")"
                            );
                        }

                        $urlChain = $sslUrl->getServerCertificateChain();

                        $validator = new OpenSsl_Certificate_Chain_Validator($urlChain);
                        $validator->validate();

                        $validatorWarnings = $validator->getWarnings();
                        $validatorErrors = $validator->getErrors();
                        foreach ($validatorWarnings as $warning) {
                            $cronLogger->with($entityId)->with($key)->with($sslUrl->getUrl())->warn($warning);
                        }
                        foreach ($validatorErrors as $error) {
                            $cronLogger->with($entityId)->with($key)->with($sslUrl->getUrl())->error($error);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $cronLogger->error($e->getMessage());
        }
        return $cronLogger->getSummaryLines();
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
}
