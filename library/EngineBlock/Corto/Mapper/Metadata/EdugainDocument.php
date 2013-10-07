<?php

class EngineBlock_Corto_Mapper_Metadata_EdugainDocument
{
    const META_TOU_COMMENT = 'Use of this metadata is subject to the Terms of Use at http://www.edugain.org/policy/metadata-tou_1_0.txt';

    private $_id;
    private $_validUntil;
    private $_eduGain;
    private $_entities;
    private $_entity;

    /**
     * @param string $id
     * @param $validUntil
     * @param boolean $eduGain
     */
    public function __construct($id, $validUntil, $eduGain)
    {
        $this->_id = $id;
        $this->_validUntil = $validUntil;
        $this->_eduGain = $eduGain;
    }

    public function map()
    {
        $rootElement[EngineBlock_Corto_XmlToArray::COMMENT_PFX] = self::META_TOU_COMMENT;
        $rootElement[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:md'] = 'urn:oasis:names:tc:SAML:2.0:metadata';
        $rootElement[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:mdui'] = 'urn:oasis:names:tc:SAML:metadata:ui';
        if ($this->_eduGain) {
            $rootElement[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:mdrpi'] = 'urn:oasis:names:tc:SAML:metadata:rpi';
        }
        $rootElement[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'validUntil'] = $this->_validUntil;

        if (isset($this->_entities)) {
            $rootElement[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID'] = $this->_id;
            $rootElement['ds:Signature'] = EngineBlock_Corto_XmlToArray::PLACEHOLDER_VALUE;
            $rootElement = $this->_mapEntities($rootElement);
        }
        else if (isset($this->_entity)) {
            $rootElement['_entityID'] = EngineBlock_Corto_XmlToArray::PLACEHOLDER_VALUE;
            $rootElement[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID'] = $this->_id;
            $rootElement['ds:Signature'] = EngineBlock_Corto_XmlToArray::PLACEHOLDER_VALUE;
            $rootElement = $this->_mapEntity($rootElement);
        }
        else {
            throw new EngineBlock_Exception("Nothing to map! Provide entities or an entity");
        }
        return $rootElement;
    }

    protected function _mapEntities($rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entities($this->_entities, $this->_eduGain);
        return $mapper->mapTo($rootElement);
    }

    protected function _mapEntity($rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity($this->_entity, $this->_eduGain);
        return $mapper->mapTo($rootElement);
    }

    public function setEntities($entities)
    {
        $this->_entities = $entities;
        return $this;
    }

    public function setEntity($entity)
    {
        $this->_entity = $entity;
        return $this;
    }
}
