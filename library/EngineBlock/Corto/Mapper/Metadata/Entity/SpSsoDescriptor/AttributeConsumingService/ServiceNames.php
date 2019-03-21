<?php

use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;

class EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor_AttributeConsumingService_ServiceNames
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
        $names = array();
        if ($this->_entity->nameNl) {
            $names['nl'] = $this->_entity->nameNl;
        }
        if ($this->_entity->nameEn) {
            $names['en'] = $this->_entity->nameEn;
        }
        if ($this->_entity->namePt) {
            $names['pt'] = $this->_entity->namePt;
        }
        if (empty($names)) {
            return $rootElement;
        }

        $rootElement['md:ServiceName'] = array();
        foreach($names as $languageCode => $value) {
            if (empty($value)) {
                continue;
            }

            $rootElement['md:ServiceName'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $languageCode,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $value
            );
        }
        return $rootElement;
    }
}
