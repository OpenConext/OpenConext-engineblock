<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractConfigurationEntity;

class EngineBlock_Corto_Mapper_Metadata_Entity_MdRpi_RegistrationPolicy
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
        $ATTRIBUTE_PREFIX = EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX;
        $registration = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->edugain->registration;

        if (!isset($rootElement['md:Extensions'])) {
            $rootElement['md:Extensions'] = array();
        }
        if (!isset($rootElement['md:Extensions']['mdrpi:RegistrationInfo'])) {
            $registrationInfo = array(
                $ATTRIBUTE_PREFIX . 'xmlns:mdrpi' => 'urn:oasis:names:tc:SAML:metadata:rpi',
                $ATTRIBUTE_PREFIX . 'registrationAuthority' => $registration->authority,
            );

            if ($this->_entity->publishInEduGainDate) {
                $registrationInstant = $this->_entity->publishInEduGainDate->format(DateTime::W3C);
                $registrationInfo[$ATTRIBUTE_PREFIX . 'registrationInstant'] =$registrationInstant;
            }

            $registrationInfo['mdrpi:RegistrationPolicy'] = array(
                array(
                    $ATTRIBUTE_PREFIX . 'xml:lang' => 'en',
                    EngineBlock_Corto_XmlToArray::VALUE_PFX => $registration->policy
                )
            );

            $rootElement['md:Extensions']['mdrpi:RegistrationInfo'] = array($registrationInfo);
        }
        return $rootElement;
    }
}