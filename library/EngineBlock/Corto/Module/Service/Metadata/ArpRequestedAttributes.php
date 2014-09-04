<?php
use OpenConext\Component\EngineBlockMetadata\Configuration\RequestedAttribute;
use OpenConext\Component\EngineBlockMetadata\Entity\AbstractConfigurationEntity;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProviderEntity;

/**
 * Add the RequestedAttributes for the AttributeConsumingService section in the SPSSODescriptor based on the ARP of the SP
 */

class EngineBlock_Corto_Module_Service_Metadata_ArpRequestedAttributes
{
    public function addRequestAttributes(AbstractConfigurationEntity $entity)
    {
        if (!$entity instanceof ServiceProviderEntity) {
            return $entity;
        }

        if (!$entity->attributeReleasePolicy) {
            return $entity;
        }

        $attributeNames = $entity->attributeReleasePolicy->getAttributeNames();

        $entity->requestedAttributes = array();
        foreach ($attributeNames as $attributeName) {
            $entity->requestedAttributes[] = new RequestedAttribute($attributeName);
        }

        return $entity;
    }


    protected function _getServiceRegistryAdapter()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getServiceRegistryAdapter();
    }
}