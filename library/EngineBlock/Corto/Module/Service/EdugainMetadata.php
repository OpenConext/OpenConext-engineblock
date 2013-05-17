<?php

class EngineBlock_Corto_Module_Service_EdugainMetadata extends EngineBlock_Corto_Module_Service_Abstract
{
    public function serve($serviceName)
    {
        // Get the configuration for EngineBlock in it's IdP role.
        $entityDetails = $this->_server->getCurrentEntity('idpMetadataService');

        $edugainEntities = array();
        foreach ($this->_server->getRemoteEntities() as $entity) {
            // Only add entities that have a SSO service registered
            if (empty($entity['PublishInEdugain'])) {
                continue;
            }

            // Generate a URL that points to EngineBlock logout service
            $transparentSlUrl = $this->_server->getUrl('singleLogoutService', $entity['EntityID']);
            // Set default value for single logout service
            if (empty($entity['SingleLogoutService'])) {
                $entity['SingleLogoutService'] = array(array());
            }
            // Override Single Logout Service information of entities with info of EngineBlock
            foreach($entity['SingleLogoutService'] as &$slService) {
                $slService['Location'] = $transparentSlUrl;
                $slService['Binding']  = $entityDetails['SingleLogoutService'][0]['Binding'];
            }

            $edugainEntities[] = $entity;
        }

        // Map the IdP configuration to a Corto XMLToArray structured document array
        $mapper = new EngineBlock_Corto_Mapper_Metadata_EdugainDocument(
            $this->_server->getNewId(),
            $this->_server->timeStamp($this->_server->getConfig('metadataValidUntilSeconds', 86400))
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
}