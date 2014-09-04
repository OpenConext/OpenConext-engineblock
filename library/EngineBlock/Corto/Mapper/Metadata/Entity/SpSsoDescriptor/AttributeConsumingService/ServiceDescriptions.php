<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractConfigurationEntity;

class EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor_AttributeConsumingService_ServiceDescriptions
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
        $descriptions = array();
        if ($this->_entity->descriptionNl) {
            $descriptions['nl'] = $this->_entity->descriptionNl;
        }
        if ($this->_entity->descriptionEn) {
            $descriptions['en'] = $this->_entity->descriptionEn;
        }
        if (empty($descriptions)) {
            return $rootElement;
        }

        $rootElement['md:ServiceDescription'] = array();
        foreach($descriptions as $languageCode => $value) {
            if (empty($value)) {
                continue;
            }

            $rootElement['md:ServiceDescription'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $languageCode,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $value
            );
        }
        return $rootElement;
    }
}