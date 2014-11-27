<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractConfigurationEntity;
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProviderEntity;

class EngineBlock_Corto_Mapper_Metadata_Entity_IdpSsoDescriptor extends EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor
{
    /**
     * @var AbstractConfigurationEntity
     */
    protected $_entity;

    public function __construct(AbstractConfigurationEntity $entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        if (!$this->_entity instanceof IdentityProviderEntity) {
            return $rootElement;
        }

        $rootElement['md:IDPSSODescriptor'] = array(
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'protocolSupportEnumeration' => "urn:oasis:names:tc:SAML:2.0:protocol",
        );

        $rootElement['md:IDPSSODescriptor'] = $this->_mapScope($rootElement['md:IDPSSODescriptor']);
        $rootElement['md:IDPSSODescriptor'] = $this->_mapUiInfo($rootElement['md:IDPSSODescriptor']);
        $rootElement['md:IDPSSODescriptor'] = $this->_mapCertificates($rootElement['md:IDPSSODescriptor']);
        $rootElement['md:IDPSSODescriptor'] = $this->_mapSingleLogoutService($rootElement['md:IDPSSODescriptor']);
        $rootElement['md:IDPSSODescriptor'] = $this->_mapNameIdFormats($rootElement['md:IDPSSODescriptor']);
        $rootElement['md:IDPSSODescriptor'] = $this->_mapSingleSignOnService($rootElement['md:IDPSSODescriptor']);

        return $rootElement;
    }

    protected function _mapSingleSignOnService(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_IdpSsoDescriptor_SingleSignOnService($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    protected function _mapScope(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_IdpSsoDescriptor_Scope($this->_entity);
        return $mapper->mapTo($rootElement);
    }
}