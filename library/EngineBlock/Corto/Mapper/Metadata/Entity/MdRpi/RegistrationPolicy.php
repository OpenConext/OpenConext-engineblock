<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;

class EngineBlock_Corto_Mapper_Metadata_Entity_MdRpi_RegistrationPolicy
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
        $ATTRIBUTE_PREFIX = EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX;
        $settings = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()
            ->getEdugainMetadataConfiguration();

        $registration = $settings['registration'];

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
