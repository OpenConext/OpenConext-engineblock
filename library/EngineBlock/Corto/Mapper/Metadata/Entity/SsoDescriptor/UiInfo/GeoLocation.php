<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_UiInfo_GeoLocation
{
    private $_entity;

    public function __construct($entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        if (empty($this->_entity['GeoLocation'])) {
            return $rootElement;
        }

        if (!isset($rootElement['md:Extensions'])) {
            $rootElement['md:Extensions'] = array();
        }
        if (!isset($rootElement['md:Extensions']['mdui:DiscoHints'])) {
            $rootElement['md:Extensions']['mdui:DiscoHints'] = array(0=>array());
        }
        $rootElement['md:Extensions']['mdui:DiscoHints'][0]['mdui:GeolocationHint'] = array(
            array(
                '__v' => $this->_entity['GeoLocation'],
            ),
        );
        return $rootElement;
    }
}