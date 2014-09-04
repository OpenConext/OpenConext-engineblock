<?php

use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProviderEntity;

class EngineBlock_Corto_Mapper_Metadata_Entity_IdpSsoDescriptor_SingleSignOnService
{
    /**
     * @var IdentityProviderEntity
     */
    private $_entity;

    public function __construct(IdentityProviderEntity $entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        // Set SSO on IDP
        if (!isset($this->_entity->singleSignOnServices)) {
            return $rootElement;
        }

        $rootElement['md:SingleSignOnService'] = array();
        foreach($this->_entity->singleSignOnServices as $service) {
            $rootElement['md:SingleSignOnService'][] = array(
                '_Binding'  => $service->binding,
                '_Location' => $service->location,
            );
        }

        return $rootElement;
    }
}