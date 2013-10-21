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

        $registration = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->edugain->registration;

        if (!isset($rootElement['md:Extensions'])) {
            $rootElement['md:Extensions'] = array();
        }
        if (!isset($rootElement['md:Extensions']['mdrpi:RegistrationInfo'])) {
            $registrationInfo = array(
                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:mdrpi' => "urn:oasis:names:tc:SAML:metadata:rpi",
                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . "registrationAuthority" => $registration->authority,

            );
            if (isset($this->_entity['PublishInEdugainDate'])) {
                $registrationInfo[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . "registrationInstant"] = $this->_entity['PublishInEdugainDate'];
            }
            $registrationInfo['mdrpi:RegistrationPolicy'] = array(
                array(
                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => "en",
                    EngineBlock_Corto_XmlToArray::VALUE_PFX => $registration->policy
                )
            );
            $rootElement['md:Extensions']['mdrpi:RegistrationInfo'] = array($registrationInfo);
        }
        return $rootElement;
    }
}