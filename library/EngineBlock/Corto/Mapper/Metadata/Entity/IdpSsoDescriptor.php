<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_IdpSsoDescriptor extends EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor
{
    protected $_entity;

    public function __construct($entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        if (!array_key_exists('SingleSignOnService', $this->_entity)) {
            return $rootElement;
        }

        $rootElement['md:IDPSSODescriptor'] = array(
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'protocolSupportEnumeration' => "urn:oasis:names:tc:SAML:2.0:protocol",
        );

        $rootElement['md:IDPSSODescriptor'] = $this->_mapUiInfo($rootElement['md:IDPSSODescriptor']);
        $rootElement['md:IDPSSODescriptor'] = $this->_mapCertificates($rootElement['md:IDPSSODescriptor']);
        $rootElement['md:IDPSSODescriptor'] = $this->_mapNameIdFormats($rootElement['md:IDPSSODescriptor']);
        $rootElement['md:IDPSSODescriptor'] = $this->_mapSingleSignOnService($rootElement['md:IDPSSODescriptor']);
        $rootElement['md:SPSSODescriptor'] = $this->_mapOrganization($rootElement['md:IDPSSODescriptor']);

        return $rootElement;
    }

    protected function _mapSingleSignOnService($rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_IdpSsoDescriptor_SingleSignOnService($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    /**
     * @param array $rootElement
     * @return array
     */
    protected function _mapOrganization(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_Organization($this->_entity);
        return $mapper->mapTo($rootElement);
    }
}