<?php

use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;

class EngineBlock_Corto_Mapper_Metadata_Entity_Organization_OrganizationNames
{
    /**
     * @var AbstractRole
     */
    private $_entity;

    public function __construct(AbstractRole $entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        $organizationNames = array();
        if ($this->_entity->organizationNl) {
            $organizationNames['nl'] = $this->_entity->organizationNl->name;
        }
        if ($this->_entity->organizationEn) {
            $organizationNames['en'] = $this->_entity->organizationEn->name;
        }
        if ($this->_entity->organizationPt) {
            $organizationNames['pt'] = $this->_entity->organizationPt->name;
        }
        if (empty($organizationNames)) {
            return $rootElement;
        }

        $rootElement['md:OrganizationName'] = array();
        foreach($organizationNames as $languageCode => $value) {
            $rootElement['md:OrganizationName'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $languageCode,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $value
            );
        }
        return $rootElement;
    }
}
