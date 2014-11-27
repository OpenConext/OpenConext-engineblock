<?php

use EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer as ServiceReplacer;
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProviderEntity;

class EngineBlock_Corto_Module_Service_EdugainMetadata extends EngineBlock_Corto_Module_Service_Abstract
{
    public function serve($serviceName)
    {
        // Get the configuration for EngineBlock in it's IdP role.

        $engineIdpEntityId = $this->_server->getUrl('idpMetadataService');
        $engineIdpEntity = $this->_server->getRepository()->fetchIdentityProviderByEntityId($engineIdpEntityId);

        $edugainEntities = array();

        $ssoServiceReplacer = new ServiceReplacer($engineIdpEntity, 'SingleSignOnService', ServiceReplacer::REQUIRED);
        $slServiceReplacer = new ServiceReplacer($engineIdpEntity, 'SingleLogoutService', ServiceReplacer::OPTIONAL);

        $remoteEntities = $this->_server->getRepository()->findEntitiesPublishableInEdugain();

        foreach ($remoteEntities as $entity) {
            // Use EngineBlock certificates
            $entity->certificates = $engineIdpEntity->certificates;

            // Ignore the NameIDFormats the IdP supports, any requests made on this endpoint will use EngineBlock
            // NameIDs, so advertise that.
            unset($entity->nameIdFormat);
            $entity->nameIdFormats = $engineIdpEntity->nameIdFormats;

            // For IdP's replace the SingleSignService with the one from EB
            if ($entity instanceof IdentityProviderEntity) {
                // Replace service locations and bindings with those of EB
                $transparentSsoUrl = $this->_server->getUrl('singleSignOnService', $entity->entityId);
                $ssoServiceReplacer->replace($entity, $transparentSsoUrl);

                $transparentSlUrl = $this->_server->getUrl('singleLogoutService');
                $slServiceReplacer->replace($entity, $transparentSlUrl);
            }
            $entity->contactPersons = $engineIdpEntity->contactPersons;

            $entity = $this->_addRequestAttributes($entity);

            $edugainEntities[] = $entity;
        }

        // Map the IdP configuration to a Corto XMLToArray structured document array
        $mapper = new EngineBlock_Corto_Mapper_Metadata_EdugainDocument(
            $this->_server->getNewId(\OpenConext\Component\EngineBlockFixtures\IdFrame::ID_USAGE_SAML2_METADATA),
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
