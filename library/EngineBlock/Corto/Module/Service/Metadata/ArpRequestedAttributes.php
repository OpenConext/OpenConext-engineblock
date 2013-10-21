<?php
/**
 * Add the RequestedAttributes for the AttributeConsumingService section in the SPSSODescriptor based on the ARP of the SP
 */

class EngineBlock_Corto_Module_Service_Metadata_ArpRequestedAttributes
{

    const URN_OASIS_NAMES_TC_SAML_2_0_ATTRNAME_FORMAT_URI = 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri';

    public function addRequestAttributes($entity)
    {

        $serviceRegistryAdapter = $this->_getServiceRegistryAdapter();
        $arp = $serviceRegistryAdapter->getArp($entity['EntityID']);
        if ($arp) {
            foreach (array_keys($arp['attributes']) as $attributeType) {
                $entity['RequestedAttributes'][] = array(
                    'Name' => $attributeType,
                    'NameFormat' => self::URN_OASIS_NAMES_TC_SAML_2_0_ATTRNAME_FORMAT_URI
                );
            }

        }
        return $entity;
    }


    protected function _getServiceRegistryAdapter()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getServiceRegistryAdapter();
    }


}