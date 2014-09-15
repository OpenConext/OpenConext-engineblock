<?php

use EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer as ServiceReplacer;
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProviderEntity;

class EngineBlock_Corto_Module_Service_IdpsMetadata extends EngineBlock_Corto_Module_Service_Abstract
{
    public function serve($serviceName)
    {
        // Fetch SP Entity Descriptor for the SP Entity ID that is fetched from the request
        $request = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest();
        $spEntityId = $request->getQueryParameter('sp-entity-id');
        if ($spEntityId) {
            // See if an sp-entity-id was specified for which we need to use sp specific metadata
            $spEntity = $this->_server->getRepository()->fetchServiceProviderByEntityId($spEntityId);
        }

        // Get the configuration for EngineBlock in it's IdP role.
        $engineIdpEntityId = $this->_server->getUrl('idpMetadataService');
        $engineIdentityProvider = $this->_server->getRepository()->fetchIdentityProviderByEntityId($engineIdpEntityId);

        $idpEntities = array();
        // Note that Shibboleth likes to see it's self in the metadata, so if an sp-entity-id was passed along
        // we make sure the first thing is the Service Provider
        if (isset($spEntity)) {
            $idpEntities[] = $spEntity;
        }

        $ssoServiceReplacer = new ServiceReplacer($engineIdentityProvider, 'SingleSignOnService', ServiceReplacer::REQUIRED);
        $slServiceReplacer  = new ServiceReplacer($engineIdentityProvider, 'SingleLogoutService', ServiceReplacer::OPTIONAL);

        if (isset($spEntity)) {
            $identityProviders = $this->_server->getRepository()->fetchIdentityProvidersByEntityId(
                $this->_server->getRepository()->findAllowedIdpEntityIdsForSp(
                    $spEntity
                )
            );
        }
        else {
            $identityProviders = $this->_server->getRepository()->findIdentityProviders();
        }

        foreach ($identityProviders as $entity) {
            // Don't add ourselves
            if ($entity->entityId === $engineIdentityProvider->entityId) {
                continue;
            }

            if ($entity->hidden) {
                continue;
            }

            // Use EngineBlock certificates
            $entity->certificates = $engineIdentityProvider->certificates;

            // Ignore the NameIDFormats the IdP supports, any requests made on this endpoint will use EngineBlock
            // NameIDs, so advertise that.
            unset($entity->nameIdFormat);
            $entity->nameIdFormats = $engineIdentityProvider->nameIdFormats;

            // Replace service locations and bindings with those of EB
            $transparentSsoUrl = $this->_server->getUrl('singleSignOnService', $entity->entityId);
            $ssoServiceReplacer->replace($entity, $transparentSsoUrl);
            $transparentSlUrl = $this->_server->getUrl('singleLogoutService');
            $slServiceReplacer->replace($entity, $transparentSlUrl);

            $entity->contactPersons = $engineIdentityProvider->contactPersons;

            $idpEntities[] = $entity;
        }

        // Map the IdP configuration to a Corto XMLToArray structured document array
        $mapper = new EngineBlock_Corto_Mapper_Metadata_EdugainDocument(
            $this->_server->getNewId(\OpenConext\Component\EngineBlockFixtures\IdFrame::ID_USAGE_SAML2_METADATA),
            $this->_server->timeStamp($this->_server->getConfig('metadataValidUntilSeconds', 86400)),
            false
        );
        $document = $mapper->setEntities($idpEntities)->map();

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
