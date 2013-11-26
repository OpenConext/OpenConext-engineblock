<?php

use EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer as ServiceReplacer;

class EngineBlock_Corto_Module_Service_EdugainMetadata extends EngineBlock_Corto_Module_Service_Abstract
{
    public function serve($serviceName)
    {
        // Get the configuration for EngineBlock in it's IdP role.
        $entityDetails = $this->_server->getCurrentEntity('idpMetadataService');

        $edugainEntities = array();

        $ssoServiceReplacer = new ServiceReplacer($entityDetails, 'SingleSignOnService', ServiceReplacer::REQUIRED);
        $slServiceReplacer = new ServiceReplacer($entityDetails, 'SingleLogoutService', ServiceReplacer::OPTIONAL);

        $remoteEntities = $this->_server->getRemoteEntities();

        foreach ($remoteEntities as $entity) {
            // Only add entities that have a SSO service registered
            if (empty($entity['PublishInEdugain'])) {
                continue;
            }

            // Use EngineBlock certificates
            $entity['certificates'] = $entityDetails['certificates'];

            // Ignore the NameIDFormats the IdP supports, any requests made on this endpoint will use EngineBlock
            // NameIDs, so advertise that.
            unset($entity['NameIDFormat']);
            $entity['NameIDFormats'] = $entityDetails['NameIDFormats'];

            // For IdP's replace the SingleSignService with the one from EB
            if (array_key_exists('SingleSignOnService', $entity)) {
                // Replace service locations and bindings with those of EB
                $transparentSsoUrl = $this->_server->getUrl('singleSignOnService', $entity['EntityID']);
                $ssoServiceReplacer->replace($entity, $transparentSsoUrl);
                $transparentSlUrl = $this->_server->getUrl('singleLogoutService');
                $slServiceReplacer->replace($entity, $transparentSlUrl);
            }
            $entity['ContactPersons'] = $entityDetails['ContactPersons'];

            // Add the SP ARP attributes for the RequestedAttribute information in the AttributeConsumingService (only if ARp is set)
            if (!array_key_exists('SingleSignOnService', $entity)) {

            }
            $entity = $this->_addRequestAttributes($entity);

            $edugainEntities[] = $entity;
        }

        // Map the IdP configuration to a Corto XMLToArray structured document array
        $mapper = new EngineBlock_Corto_Mapper_Metadata_EdugainDocument(
            $this->_server->getNewId(),
            $this->_server->timeStamp($this->_server->getConfig('metadataValidUntilSeconds', 86400)),
            true
        );
        $document = $mapper->setEntities($edugainEntities)->map();

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


    protected function _addRequestAttributes($entity)
    {
        $arpRequestedAttributes = new EngineBlock_Corto_Module_Service_Metadata_ArpRequestedAttributes();
        return $arpRequestedAttributes->addRequestAttributes($entity);
    }


}