<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_IdpSsoDescriptor_SingleSignOnService
{
    private $_entity;

    public function __construct($entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        // Set SSO on IDP
        if (!isset($this->_entity['SingleSignOnService'])) {
            return $rootElement;
        }
        $rootElement['md:SingleSignOnService'] = array(
            '_Binding'  => $this->_entity['SingleSignOnService']['Binding'],
            '_Location' => $this->_entity['SingleSignOnService']['Location'],
        );
        return $rootElement;
    }
}