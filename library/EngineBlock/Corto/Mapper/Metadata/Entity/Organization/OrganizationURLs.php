<?php

use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;

class EngineBlock_Corto_Mapper_Metadata_Entity_Organization_OrganizationURLs
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
        $organizationUrls = array();
        if ($this->_entity->organizationNl) {
            $organizationUrls['nl'] = $this->_entity->organizationNl->url;
        }
        if ($this->_entity->organizationEn) {
            $organizationUrls['en'] = $this->_entity->organizationEn->url;
        }
        if ($this->_entity->organizationPt) {
            $organizationUrls['pt'] = $this->_entity->organizationPt->url;
        }
        if (empty($organizationUrls)) {
            return $rootElement;
        }

        $rootElement['md:OrganizationURL'] = array();
        foreach($organizationUrls as $languageCode => $value) {
            $rootElement['md:OrganizationURL'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $languageCode,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $value
            );
        }
        return $rootElement;
    }
}
