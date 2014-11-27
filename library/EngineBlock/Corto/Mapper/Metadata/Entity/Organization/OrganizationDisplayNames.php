<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractConfigurationEntity;

class EngineBlock_Corto_Mapper_Metadata_Entity_Organization_OrganizationDisplayNames
{
    /**
     * @var AbstractConfigurationEntity
     */
    private $_entity;

    public function __construct(AbstractConfigurationEntity $entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        $organizationDisplayNames = array();
        if ($this->_entity->organizationNl) {
            $organizationDisplayNames['nl'] = $this->_entity->organizationNl->displayName;
        }
        if ($this->_entity->organizationEn) {
            $organizationDisplayNames['en'] = $this->_entity->organizationEn->displayName;
        }
        if (empty($organizationDisplayNames)) {
            return $rootElement;
        }

        $rootElement['md:OrganizationDisplayName'] = array();
        foreach($organizationDisplayNames as $languageCode => $value) {
            $rootElement['md:OrganizationDisplayName'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $languageCode,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $value
            );
        }
        return $rootElement;
    }
}