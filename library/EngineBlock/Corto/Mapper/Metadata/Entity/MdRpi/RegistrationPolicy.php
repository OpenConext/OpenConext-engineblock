<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_MdRpi_RegistrationPolicy
{
    private $_entity;

    public function __construct($entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {

            if (!isset($rootElement['md:Extensions'])) {
                $rootElement['md:Extensions'] = array();
            }
            if (!isset($rootElement['md:Extensions']['mdrpi:RegistrationInfo'])) {
                $rootElement['md:Extensions']['mdrpi:RegistrationInfo'] = array(0=>array(
                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:mdrpi' => "urn:oasis:names:tc:SAML:metadata:rpi",
                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . "registrationAuthority" => "http://www.surfconext.nl/",
//                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . "registrationInstant" => "2013-05-03T16:31:26Z"
                ));
            }
            $rootElement['md:Extensions']['mdrpi:RegistrationInfo'][0]['mdrpi:RegistrationPolicy'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => "en",
                EngineBlock_Corto_XmlToArray::VALUE_PFX => "https://wiki.surfnetlabs.nl/display/eduGAIN/EduGAIN"
            );
        return $rootElement;
    }
}