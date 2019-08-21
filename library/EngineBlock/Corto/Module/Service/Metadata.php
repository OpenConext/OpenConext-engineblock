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

use EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer as ServiceReplacer;
use OpenConext\EngineBlock\Metadata\MetadataRepository\EntityNotFoundException;
use OpenConext\EngineBlockBundle\Stepup\StepupEntity;

class EngineBlock_Corto_Module_Service_Metadata extends EngineBlock_Corto_Module_Service_Abstract
{
    public function serve($serviceName)
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();
        $container = $application->getDiContainer();

        // Get the configuration for EngineBlock in it's IdP / SP role without the VO.
        $this->_server->setProcessingMode();
        $engineEntityId = $this->_server->getUrl($serviceName);
        $this->_server->unsetProcessingMode();

        $log = EngineBlock_ApplicationSingleton::getInstance()->getLogInstance();

        // The requested service can be both a SP and an IdP. Based on the serviceName we know what entity type to load.
        switch ($serviceName) {
            case 'idpMetadataService':
                $engineEntity = $this->_server->getRepository()->fetchIdentityProviderByEntityId($engineEntityId);
                break;
            case 'spMetadataService':
                $engineEntity = $this->_server->getRepository()->fetchServiceProviderByEntityId($engineEntityId);
                break;
            case 'stepupMetadataService':
                $engineEntity = $container->getStepupServiceProvider($this->_server);
                break;
            default:
                // If an unsupported serviceName is used, first try to resolve a SP, then try IdP. This is a fallback
                // and wil probably return the correct entity. In the case of IdP/SP with the same EntityID this might
                // not be the case. A warning is logged to warn us about this situation.
                $log->warning(
                    sprintf(
                        "Trying to find EngineBlock entity for serviceName '%s'. Please update the switch in '%s'",
                        $serviceName,
                        'EngineBlock_Corto_Module_Service_Metadata::serve'
                    )
                );
                try {
                    $engineEntity = $this->_server->getRepository()->fetchServiceProviderByEntityId($engineEntityId);
                } catch (EntityNotFoundException $e) {
                    $engineEntity = $this->_server->getRepository()->fetchIdentityProviderByEntityId($engineEntityId);
                }
                break;
        }

        // Override the EntityID and SSO location to optionally append VO id
        $externalEngineEntityId = $this->_server->getUrl($serviceName);
        $engineEntity->entityId = $externalEngineEntityId;

        if ($serviceName === 'idpMetadataService') {
            $ssoServiceReplacer = new ServiceReplacer($engineEntity, 'SingleSignOnService', ServiceReplacer::REQUIRED);
            $ssoLocation = $this->_server->getUrl('singleSignOnService');
            $ssoServiceReplacer->replace($engineEntity, $ssoLocation);
        }

        // Override Single Logout Service Location with generated url
        $slServiceReplacer = new ServiceReplacer($engineEntity, 'SingleLogoutService', ServiceReplacer::OPTIONAL);
        $slLocation = $this->_server->getUrl('singleLogoutService');
        $slServiceReplacer->replace($engineEntity, $slLocation);

        // Map the IdP configuration to a Corto XMLToArray structured document array
        $mapper = new EngineBlock_Corto_Mapper_Metadata_EdugainDocument(
            $this->_server->getNewId(EngineBlock_Saml2_IdGenerator::ID_USAGE_SAML2_METADATA),
            $this->_server->timeStamp($this->_server->getConfig('metadataValidUntilSeconds', 86400)),
            false
        );
        $document = $mapper->setEntity($engineEntity)->map();

        // Sign the document
        $document = $this->_server->sign($document);

        // Convert the document to XML
        $xml = EngineBlock_Corto_XmlToArray::array2xml($document);

        // If debugging is enabled then validate it according to the schema
        if ($this->_server->getConfig('debug', false)) {
            $validator = new EngineBlock_Xml_Validator(
                'http://docs.oasis-open.org/security/saml/v2.0/saml-schema-metadata-2.0.xsd'
            );
            $validator->validate($xml);
        }

        // The spec dictates we use a custom mimetype, but debugging is easier with a normal mimetype
        // also no single SP / IdP complains over this.
        //$this->_server->sendHeader('Content-Type', 'application/samlmetadata+xml');
        $this->_server->sendHeader('Content-Type', 'application/xml');
        $this->_server->sendOutput($xml);
    }
}
