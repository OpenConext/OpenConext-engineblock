<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_UiInfo_Logo
{
    private $_entity;

    public function __construct($entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        if (!isset($this->_entity['Logo'])) {
            return $rootElement;
        }

        $hasLogoHeight = (isset($this->_entity['Logo']['Height']) && $this->_entity['Logo']['Height']);
        $hasLogoWidth  = (isset($this->_entity['Logo']['Width'])  && $this->_entity['Logo']['Width']);
        if (!$hasLogoHeight || !$hasLogoWidth) {
            // @todo warn here!
            return $rootElement;
        }

        if (!isset($rootElement['md:Extensions'])) {
            $rootElement['md:Extensions'] = array();
        }
        if (!isset($rootElement['md:Extensions']['mdui:UIInfo'])) {
            $rootElement['md:Extensions']['mdui:UIInfo'] = array(0=>array());
        }
        $rootElement['md:Extensions']['mdui:UIInfo'][0]['mdui:Logo'] = array(
            array(
                '_height' => $this->_entity['Logo']['Height'],
                '_width'  => $this->_entity['Logo']['Width'],
                '__v'     => $this->_entity['Logo']['URL'],
            ),
        );
        return $rootElement;
    }
}