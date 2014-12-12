<?php

use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProviderEntity;

class EngineBlock_Corto_Mapper_Metadata_Entity_IdpSsoDescriptor_Scope
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
        if (empty($this->_entity->shibMdScopes)) {
            return $rootElement;
        }
        if (!isset($rootElement['md:Extensions'])) {
            $rootElement['md:Extensions'] = array();
        }
        foreach ($this->_entity->shibMdScopes as $scope) {
            $rootElement['md:Extensions']['shibmd:Scope'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:shibmd' => 'urn:mace:shibboleth:metadata:1.0',
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'regexp' => $scope->regexp ? 'true' : 'false' ,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $scope->allowed,
            );
        }
        return $rootElement;
    }
}