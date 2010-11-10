<?php
 
class EngineBlock_Corto_ServiceRegistry_Adapter 
{
    /**
     * @var EngineBlock_ServiceRegistry_Client
     */
    protected $_serviceRegistry;

    public function __construct($serviceRegistry)
    {
        $this->_serviceRegistry = $serviceRegistry;
    }

    /**
     * Given a list of (SAML2) entities, filter out the idps that are not allowed
     * for the given Service Provider.
     *
     * @todo makes a call for EVERY idp to the service registry,
     *       the SR should just implement 1 call for all allowed IdPs
     *
     * @param  $entities
     * @param  $spEntityId
     * @return array Filtered entities
     */
    public function filterEntitiesBySp(array $entities, $spEntityId)
    {
        foreach ($entities as $entityId => $entityData) {
            if (isset($entityData['SingleSignOnService'])) {
                // entity is an idp
                if (!$this->_serviceRegistry->isConnectionAllowed(
                    $spEntityId,
                    $entityId
                )) {
                    unset($entities[$entityId]);
                }
            }
        }
        return $entities;
    }
    
    public function isConnectionAllowed($spEntityId, $idpEntityId)
    {
        return $this->_serviceRegistry->isConnectionAllowed($spEntityId, $idpEntityId);
    }

    public function getRemoteMetaData()
    {
        return $this->_getRemoteSPsMetaData() + $this->_getRemoteIdPsMetadata();
    }

    protected function _getRemoteIdPsMetadata()
    {
        $metadata = array();
        $idPs = $this->_serviceRegistry->getIdpList();
        foreach ($idPs as $idPEntityId => $idP) {
            try {
                $metadata[$idPEntityId] = self::convertServiceRegistryEntityToCortoEntity($idP);
            } catch (Exception $e) {
                // Whoa, something went wrong trying to convert the SR entity to a Corto entity
                // We can't use this entity, but we can continue after we've reported
                // this serious error
                $application = EngineBlock_ApplicationSingleton::getInstance();
                $application->reportError($e);
                continue;
            }
        }
        return $metadata;
    }

    protected function _getRemoteSPsMetaData()
    {
        $metadata = array();
        $sPs = $this->_serviceRegistry->getSPList();
        foreach ($sPs as $sPEntityId => $sP) {
            try {
                $metadata[$sPEntityId] = self::convertServiceRegistryEntityToCortoEntity($sP);
            } catch (Exception $e) {
                // Whoa, something went wrong trying to convert the SR entity to a Corto entity
                // We can't use this entity, but we can continue after we've reported
                // this serious error
                $application = EngineBlock_ApplicationSingleton::getInstance();
                $application->reportError($e);
                continue;
            }
        }
        return $metadata;
    }

    protected static function convertServiceRegistryEntityToCortoEntity($serviceRegistryEntity)
    {
        $serviceRegistryEntity = self::convertServiceRegistryEntityToMultiDimensionalArray($serviceRegistryEntity);

        $cortoEntity = array();
        if (isset($serviceRegistryEntity['AssertionConsumerService'][0]['Location'])) {
            $cortoEntity['AssertionConsumerService'] = array(
                'Binding'  => $serviceRegistryEntity['AssertionConsumerService'][0]['Binding'],
                'Location' => $serviceRegistryEntity['AssertionConsumerService'][0]['Location'],
            );
            $cortoEntity['WantsAssertionsSigned'] = true;
        }
        if (isset($serviceRegistryEntity['SingleSignOnService'][0]['Location'])) {
            $cortoEntity['SingleSignOnService'] = array(
                'Binding'   => $serviceRegistryEntity['SingleSignOnService'][0]['Binding'],
                'Location'  => $serviceRegistryEntity['SingleSignOnService'][0]['Location'],
            );
        }
        if (isset($serviceRegistryEntity['certData']) && $serviceRegistryEntity['certData']) {
            $cortoEntity['certificates'] = array(
                'public' => EngineBlock_X509Certificate::getPemFromCertData($serviceRegistryEntity['certData']),
            );
        }
        if (isset($serviceRegistryEntity['name'])) {
            if ($serviceRegistryEntity['name']['en']==="EngineBlock Dev Test SP") {
                throw new Exception('Whoa, cant use this sp man');
            }
            $cortoEntity['Name'] = $serviceRegistryEntity['name'];
        }
        if (isset($serviceRegistryEntity['description'])) {
            $cortoEntity['Description'] = $serviceRegistryEntity['description'];
        }
        if (isset($serviceRegistryEntity['displayName'])) {
            $cortoEntity['DisplayName'] = $serviceRegistryEntity['displayName'];
        }
        if (isset($serviceRegistryEntity['logo'][0]['href'])) {
            $cortoEntity['Logo'] = array(
                'Href' => $serviceRegistryEntity['logo'][0]['href'],
                'Height' => $serviceRegistryEntity['logo'][0]['height'],
                'Width' => $serviceRegistryEntity['logo'][0]['width'],
                'URL' => $serviceRegistryEntity['logo'][0]['url'],
            );
        }
        if (isset($serviceRegistryEntity['geoLocation'])) {
            $cortoEntity['GeoLocation'] = $serviceRegistryEntity['geoLocation'];
        }
        if (isset($serviceRegistryEntity['redirect']['sign'])) {
            $cortoEntity['WantsAuthnRequestsSigned'] = (bool)$serviceRegistryEntity['redirect']['sign'];
        }
        if (isset($serviceRegistryEntity['redirect']['validate'])) {
            $cortoEntity['WantsAuthnRequestsSigned'] = (bool)$serviceRegistryEntity['redirect']['validate'];
            $cortoEntity['WantsResponsesSigned']     = (bool)$serviceRegistryEntity['redirect']['validate'];
        }
        if (isset($serviceRegistryEntity['organization']['OrganizationName'])) {
            $cortoEntity['Organization']['Name'] = $serviceRegistryEntity['organization']['OrganizationName'];
        }
        if (isset($serviceRegistryEntity['organization']['OrganizationDisplayName'])) {
            $cortoEntity['Organization']['DisplayName'] = $serviceRegistryEntity['organization']['OrganizationDisplayName'];
        }
        if (isset($serviceRegistryEntity['organization']['OrganizationURL'])) {
            $cortoEntity['Organization']['URL'] = $serviceRegistryEntity['organization']['OrganizationURL'];
        }
        return $cortoEntity;
    }

    public static function convertServiceRegistryEntityToMultiDimensionalArray($entity)
    {
        $newEntity = array();
        foreach ($entity as $name => $value) {
            $colonSeparatedParts = explode(':', $name);
            if (count($colonSeparatedParts) > 1) {
                $arrayPointer = &$newEntity;
                foreach ($colonSeparatedParts as $subId) {
                    if (!isset($arrayPointer[$subId])) {
                        $arrayPointer[$subId] = array();
                    }
                    $arrayPointer = &$arrayPointer[$subId];
                }
                $arrayPointer = $value;
                unset($arrayPointer);
                continue;
            }

            $dotSeparatedParts = explode('.', $name);
            if (count($dotSeparatedParts) > 1) {
                $arrayPointer = &$newEntity;
                foreach ($dotSeparatedParts as $subId) {
                    if (!isset($arrayPointer[$subId])) {
                        $arrayPointer[$subId] = array();
                    }
                    $arrayPointer = &$arrayPointer[$subId];
                }
                $arrayPointer = $value;
                unset($arrayPointer);
                continue;
            }

            $newEntity[$name] = $value;
        }
        return $newEntity;
    }
}
