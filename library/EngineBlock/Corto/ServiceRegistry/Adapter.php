<?php
 
class EngineBlock_Corto_ServiceRegistry_Adapter 
{
    /**
     * @var EngineBlock_ServiceRegistry
     */
    protected $_serviceRegistry;

    public function __construct($serviceRegistry)
    {
        $this->_serviceRegistry = $serviceRegistry;
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
            $metadata[$idPEntityId] = self::convertServiceRegistryEntityToCortoEntity($idP);
        }
        return $metadata;
    }

    protected function _getRemoteSPsMetaData()
    {
        $metadata = array();
        $sPs = $this->_serviceRegistry->getSPList();
        foreach ($sPs as $sPEntityId => $sP) {
            $metadata[$sPEntityId] = self::convertServiceRegistryEntityToCortoEntity($sP);
        }
        return $metadata;
    }

    protected static function convertServiceRegistryEntityToCortoEntity($serviceRegistryEntity)
    {
        $serviceRegistryEntity = self::convertServiceRegistryEntityToMultiDimensionalArray($serviceRegistryEntity);

        $cortoEntity = array();
        if (isset($serviceRegistryEntity['AssertionConsumerService'][0]['Location'])) {
            $cortoEntity['AssertionConsumerService'] = array(
                $serviceRegistryEntity['AssertionConsumerService'][0]['Binding'],
                $serviceRegistryEntity['AssertionConsumerService'][0]['Location'],
            );
            $cortoEntity['WantsAssertionsSigned'] = true;
        }
        if (isset($serviceRegistryEntity['SingleSignOnService'][0]['Location'])) {
            $cortoEntity['SingleSignOnService'] = array(
                'Binding'   => $serviceRegistryEntity['SingleSignOnService'][0]['Binding'],
                'Location'  => $serviceRegistryEntity['SingleSignOnService'][0]['Location'],
            );
        }
        if (isset($serviceRegistryEntity['certData'])) {
            $cortoEntity['certificates'] = array(
                'public' => $serviceRegistryEntity['certData'],
            );
        }
        if (isset($serviceRegistryEntity['name'])) {
            $cortoEntity['Name'] = $serviceRegistryEntity['name'];
        }
        if (isset($serviceRegistryEntity['description'])) {
            $cortoEntity['Description'] = $serviceRegistryEntity['description'];
        }
        if (isset($serviceRegistryEntity['redirect']['sign'])) {
            $cortoEntity['WantsAuthnRequestsSigned'] = (bool)$serviceRegistryEntity['redirect']['sign'];
        }
        if (isset($serviceRegistryEntity['redirect']['validate'])) {
            $cortoEntity['WantsAuthnRequestsSigned'] = (bool)$serviceRegistryEntity['redirect']['validate'];
            $cortoEntity['WantsResponsesSigned']     = (bool)$serviceRegistryEntity['redirect']['validate'];
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
