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

        $rootElement['md:SingleSignOnService'] = array();
        foreach($this->_entity['SingleSignOnService'] as $service) {
            $rootElement['md:SingleSignOnService'][] = array(
                '_Binding'  => $service['Binding'],
                '_Location' => $service['Location'],
            );
        }

        return $rootElement;
    }
}