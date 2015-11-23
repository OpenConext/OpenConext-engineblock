<?php

use EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer as ServiceReplacer;
use OpenConext\Component\EngineBlockFixtures\IdFrame;

class EngineBlock_Corto_Module_Service_Metadata extends EngineBlock_Corto_Module_Service_Abstract
{
    public function serve($serviceName)
    {
        // Get the configuration for EngineBlock in it's IdP / SP role without the VO.
        $this->_server->setProcessingMode();
        $engineEntityId = $this->_server->getUrl($serviceName);
        $this->_server->unsetProcessingMode();

        $engineEntity = $this->_server->getRepository()->fetchEntityByEntityId($engineEntityId);

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
            $this->_server->getNewId(IdFrame::ID_USAGE_SAML2_METADATA),
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
