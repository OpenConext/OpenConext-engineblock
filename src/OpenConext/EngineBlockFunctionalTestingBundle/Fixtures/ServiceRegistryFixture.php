<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures;

use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore\AbstractDataStore;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore\FileFlags;
use SAML2_Const;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ServiceRegistryFixture
{
    const TYPE_SP = 1;
    const TYPE_IDP = 2;

    protected $fixture;
    protected $fileFlags;
    protected $directory;
    protected $data;

    public function __construct(AbstractDataStore $dataStore, FileFlags $fileFlags, $directory)
    {
        $this->fixture = $dataStore;
        $this->fileFlags = $fileFlags;
        $this->directory = $directory;

        $this->data = $dataStore->load();
    }

    public function reset()
    {
        $this->data = [];
        $files = glob($this->directory . 'arp-*');
        foreach ($files as $file) {
            unlink($file);
        }
        return $this;
    }

    public function registerSp($name, $entityId, $acsLocation, $certData = '')
    {
        $this->data[$entityId] = [
            'workflowState' => 'prodaccepted',
            'entityId'      => $entityId,
            'name:en' => $name,
            'name:nl' => $name,
            'displayName:en' => $name,
            'displayName:nl' => $name,
            'AssertionConsumerService:0:Binding'  => SAML2_Const::BINDING_HTTP_POST,
            'AssertionConsumerService:0:Location' => $acsLocation,
        ];
        if (!empty($certData)) {
            $this->data[$entityId]['certData'] = $certData;
        }
        return $this;
    }

    public function spRequiresPolicyEnforcementDecision($entityId)
    {
        $this->data[$entityId]['coin:policy_enforcement_decision_required'] = true;

        return $this;
    }

    public function requireAttributeAggregation($entityId)
    {
        $this->data[$entityId]['coin:attribute_aggregation_required'] = true;
        return $this;
    }

    public function registerIdp($name, $entityId, $ssoLocation, $certData = '')
    {
        $this->data[$entityId] = [
            'workflowState' => 'prodaccepted',
            'entityId'      => $entityId,
            'name:en' => $name,
            'name:nl' => $name,
            'displayName:en' => $name,
            'displayName:nl' => $name,
            'SingleSignOnService:0:Binding'  => SAML2_Const::BINDING_HTTP_POST,
            'SingleSignOnService:0:Location' => $ssoLocation,
            'SingleSignOnService:1:Binding'  => SAML2_Const::BINDING_HTTP_REDIRECT,
            'SingleSignOnService:1:Location' => $ssoLocation,
        ];
        if (!empty($certData)) {
            $this->data[$entityId]['certData'] = $certData;
        }
        return $this;
    }

    public function move($fromEntityId, $toEntityId)
    {
        $this->data[$toEntityId] = $this->data[$fromEntityId];
        $this->remove($fromEntityId);

        $this->data[$toEntityId]['entityId'] = $toEntityId;

        return $this;
    }

    public function remove($entityId)
    {
        unset($this->data[$entityId]);
        return $this;
    }

    public function setEntitySsoLocation($entityId, $ssoLocation)
    {
        $this->data[$entityId]['SingleSignOnService:0:Location'] = $ssoLocation;
        return $this;
    }

    public function setEntityAcsLocation($entityId, $acsLocation)
    {
        $this->data[$entityId]['AssertionConsumerService:0:Location'] = $acsLocation;
        return $this;
    }

    public function setEntityNoConsent($entityId)
    {
        $this->data[$entityId]['coin:no_consent_required'] = true;
        return $this;
    }

    public function setEntityWantsSignature($entityId)
    {
        $this->data[$entityId]['redirect.sign'] = true;
        return $this;
    }

    public function setEntityTrustedProxy($entityId)
    {
        $this->data[$entityId]['coin:trusted_proxy'] = true;
        return $this;
    }

    public function setEntityManipulation($entityId, $manipulation)
    {
        $this->data[$entityId]['manipulation'] = $manipulation;
        return $this;
    }

    public function setEntityNameIdFormatUnspecified($entityId)
    {
        $this->data[$entityId]['NameIDFormat'] = SAML2_Const::NAMEID_UNSPECIFIED;
        return $this;
    }

    public function setEntityNameIdFormatPersistent($entityId)
    {
        $this->data[$entityId]['NameIDFormat'] = SAML2_Const::NAMEID_PERSISTENT;
        return $this;
    }

    public function setEntityNameIdFormatTransient($entityId)
    {
        $this->data[$entityId]['NameIDFormat'] = SAML2_Const::NAMEID_TRANSIENT;
        return $this;
    }

    public function addSpsFromJsonExport($spsConfigExportUrl)
    {
        $this->addEntitiesFromJsonConfigExport($spsConfigExportUrl);
        return $this;
    }

    public function addIdpsFromJsonExport($idpsConfigExportUrl)
    {
        $this->addEntitiesFromJsonConfigExport($idpsConfigExportUrl);
        return $this;
    }

    /**
     * @param $configExportUrl
     * @param int $type
     * @SuppressWarnings(PMD)
     */
    protected function addEntitiesFromJsonConfigExport($configExportUrl, $type = self::TYPE_SP)
    {
        echo "Downloading ServiceRegistry configuration from: '{$configExportUrl}'..." . PHP_EOL;
        $data = file_get_contents($configExportUrl);
        if (!$data) {
            throw new \RuntimeException('Unable to get data from: ' . $configExportUrl);
        }
        $entities = json_decode($data, true);
        if ($entities === false) {
            throw new \RuntimeException('Unable to decode json: ' . $data);
        }

        foreach ($entities as $entity) {
            $entity = $this->flattenArray($entity);
            $entity['workflowState'] = 'prodaccepted';

            $entityId = $entity['entityid'];

            $this->data[$entityId] = $entity;

            if (!empty($entity['allowedEntities'])) {
                $this->whitelist($entityId);

                foreach ($entity['allowedEntities'] as $allowedEntityId) {
                    if ($type === self::TYPE_SP) {
                        $this->allow($entityId, $allowedEntityId);
                    } else {
                        $this->allow($allowedEntityId, $entityId);
                    }
                }
            }

            if (!empty($entity['blockedEntities'])) {
                $this->blacklist($entityId);
                foreach ($entity['blockedEntities'] as $blockedEntityId) {
                    $this->block($entityId, $blockedEntityId);
                    if ($type === self::TYPE_SP) {
                        $this->block($entityId, $blockedEntityId);
                    } else {
                        $this->block($blockedEntityId, $entityId);
                    }
                }
            }
        }
    }

    protected function flattenArray(array $array, array $newArray = [], $prefix = false)
    {
        foreach ($array as $name => $value) {
            if (is_array($value)) {
                $newArray = $this->flattenArray($value, $newArray, $prefix . $name . ':');
            } else {
                $newArray[$prefix . $name] = $value;
            }
        }
        return $newArray;
    }

    public function blacklist($entityId)
    {
        $this->fileFlags->off('whitelisted-' . md5($entityId), $entityId);
        return $this;
    }

    public function whitelist($entityId)
    {
        $this->fileFlags->on('whitelisted-' . md5($entityId), $entityId);
        return $this;
    }

    public function allow($spEntityId, $idpEntityId)
    {
        $this->fileFlags->off('connection-forbidden-' . md5($spEntityId) . '-' . md5($idpEntityId));
        $this->fileFlags->on(
            'connection-allowed-' . md5($spEntityId) . '-' . md5($idpEntityId),
            $spEntityId . ' - ' . $idpEntityId
        );
        return $this;
    }

    public function block($spEntityId, $idpEntityId)
    {
        $this->fileFlags->off('connection-allowed-' . md5($spEntityId) . '-' . md5($idpEntityId));
        $this->fileFlags->on(
            'connection-forbidden-' . md5($spEntityId) . '-' . md5($idpEntityId),
            $spEntityId . ' - ' . $idpEntityId
        );
        return $this;
    }

    public function allowNoAttributeValues($entityId)
    {
        $this->data[$entityId]['arp'] = [];

        return $this;
    }

    public function allowAttributeValue($entityId, $arpAttribute, $attributeValue, $attributeSource = null)
    {
        if (!isset($this->data[$entityId]['arp'])) {
            $this->data[$entityId]['arp'] = [];
        }

        // Save allowed value
        if (!isset($this->data[$entityId]['arp'][$arpAttribute])) {
            $this->data[$entityId]['arp'][$arpAttribute] = [];
        }

        if ($attributeSource) {
            $arpRule = [
                'value' => $attributeValue,
                'source' => $attributeSource,
            ];
        } else {
            $arpRule = $attributeValue;
        }

        $this->data[$entityId]['arp'][$arpAttribute][] = $arpRule;

        return $this;
    }

    public function setWorkflowState($entityId, $workflowState)
    {
        $this->data[$entityId]['workflowState'] = $workflowState;

        return $this;
    }

    public function save()
    {
        $this->fixture->save($this->data);
    }

    public function __destruct()
    {
        $this->save();
    }
}
