<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_Organization
{
    private $_entity;

    public function __construct($entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        // All Child elements are required so element will be rendered only if all of then are filled in
        if (empty($this->_entity['Organization']['Name']) ||
            empty($this->_entity['Organization']['DisplayName']) ||
            empty($this->_entity['Organization']['URL'])) {
            return $rootElement;
        }
        $rootElement['md:Organization'] = array();

        $rootElement['md:Organization'] = $this->_mapOrganizationNames($rootElement['md:Organization']);
        $rootElement['md:Organization'] = $this->_mapOrganizationDisplayNames($rootElement['md:Organization']);
        $rootElement['md:Organization'] = $this->_mapOrganizationURLs($rootElement['md:Organization']);
        return $rootElement;
    }

    /**
     * @param array $rootElement
     * @return array
     */
    protected function _mapOrganizationNames(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_Organization_OrganizationNames($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    /**
     * @param array $rootElement
     * @return array
     */
    protected function _mapOrganizationDisplayNames(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_Organization_OrganizationDisplayNames($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    /**
     * @param array $rootElement
     * @return array
     */
    protected function _mapOrganizationURLs(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_Organization_OrganizationURLs($this->_entity);
        return $mapper->mapTo($rootElement);
    }
}