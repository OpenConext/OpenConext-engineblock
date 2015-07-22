<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractRole;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;

class EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor extends EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor
{
    /**
     * @var AbstractRole
     */
    protected $_entity;

    public function __construct(AbstractRole $entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        if (!$this->_entity instanceof ServiceProvider) {
            return $rootElement;
        }

        $rootElement['md:SPSSODescriptor'] = array(
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'protocolSupportEnumeration' => "urn:oasis:names:tc:SAML:2.0:protocol",
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'WantAssertionsSigned' => 'true'
        );

        $rootElement['md:SPSSODescriptor'] = $this->_mapUiInfo($rootElement['md:SPSSODescriptor']);
        $rootElement['md:SPSSODescriptor'] = $this->_mapCertificates($rootElement['md:SPSSODescriptor']);
        $rootElement['md:SPSSODescriptor'] = $this->_mapSingleLogoutService($rootElement['md:SPSSODescriptor']);
        $rootElement['md:SPSSODescriptor'] = $this->_mapAssertionConsumerServices($rootElement['md:SPSSODescriptor']);
        $rootElement['md:SPSSODescriptor'] = $this->_mapAttributeConsumingService($rootElement['md:SPSSODescriptor']);

        return $rootElement;
    }

    protected function _mapAssertionConsumerServices(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor_AssertionConsumerServices($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    protected function _mapAttributeConsumingService(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor_AttributeConsumingService($this->_entity);
        return $mapper->mapTo($rootElement);
    }

}
