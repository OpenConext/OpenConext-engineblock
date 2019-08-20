<?php

/**
 * Copyright 2014 SURFnet B.V.
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

class EngineBlock_Corto_Mapper_Metadata_Entity_MdRpi_PublicationInfo
{

    public function mapTo(array $rootElement)
    {
        $settings = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()
            ->getEdugainMetadataConfiguration();

        $publication = $settings['publication'];

        if (!isset($rootElement['md:Extensions'])) {
            $rootElement['md:Extensions'] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . "xmlns:mdrpi" => "urn:oasis:names:tc:SAML:metadata:rpi"
            );
        }
        if (!isset($rootElement['md:Extensions']['mdrpi:PublicationInfo'])) {
            $publicationInfo = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . "creationInstant" => date(DateTime::W3C ),
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . "publisher" => $publication->publisher
            );
            $publicationInfo['mdrpi:UsagePolicy'] = array(
                array(
                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => "en",
                    EngineBlock_Corto_XmlToArray::VALUE_PFX => $publication->policy
                )
            );
            $rootElement['md:Extensions']['mdrpi:PublicationInfo'] = array($publicationInfo);

        }
        return $rootElement;
    }
}
