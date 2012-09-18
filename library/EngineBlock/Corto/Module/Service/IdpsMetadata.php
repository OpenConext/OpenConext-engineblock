<?php

class EngineBlock_Corto_Module_Service_IdpsMetadata extends EngineBlock_Corto_Module_Service_Abstract
{
    public function serve($serviceName)
    {
        // See if an sp-entity-id was specified for which we need to use alternate keys (key rollover)
        try {
            // See if an sp-entity-id was specified for which we need to use alternate keys (key rollover)
            $alternateKeys = $this->_getAlternateKeys();
        } catch (EngineBlock_Corto_ProxyServer_UnknownRemoteEntityException $e) {
            $spEntityId = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest()->getQueryParameter('sp-entity-id');
            $this->_server->redirect(
                '/authentication/feedback/unknown-service-provider?entity-id=' . urlencode($spEntityId),
                "Unknown SP!");
            return;
        }
        if ($alternateKeys) {
            $entityDetails['certificates'] = $alternateKeys;
        }

        // Get the configuration for EngineBlock in it's IdP role.
        $entityDetails = $this->_getCurrentEntity('idpMetadataService');

        $idpEntities = array();
        foreach ($this->_server->getRemoteEntities() as $entityId => $entity) {
            // Don't add ourselves
            if ($entity['EntityID'] === $entityDetails['EntityID']) {
                continue;
            }

            // Only add entities that have a SSO service registered
            if (!isset($entity['SingleSignOnService'])) {
                continue;
            }

            // Use EngineBlock certificates
            $entity['certificates'] = $entityDetails['certificates'];

            // Ignore the NameIDFormats the IdP supports, any requests made on this endpoint will use EngineBlock
            // NameIDs, so advertise that.
            unset($entity['NameIDFormat']);
            $entity['NameIDFormats'] = $entityDetails['NameIDFormats'];

            // Generate a URL that points to EngineBlock, but with the given IdP preselected.
            $transparentSsoUrl = $this->_server->getUrl('singleSignOnService', $entity['EntityID']);
            $entity['SingleSignOnService']['Location'] = $transparentSsoUrl;
            $entity['SingleSignOnService']['Binding']  = $entityDetails['SingleSignOnService']['Binding'];

            $entity['ContactPersons'] = $entityDetails['ContactPersons'];

            $idpEntities[] = $entity;
        }

        // Map the IdP configuration to a Corto XMLToArray structured document array
        $mapper = new EngineBlock_Corto_Mapper_Metadata_EdugainDocument(
            $this->_server->getNewId(),
            $this->_server->timeStamp($this->_server->getConfig('metadataValidUntilSeconds', 86400))
        );
        $document = $mapper->setEntities($idpEntities)->map();

        // Sign the document
        $document = $this->_server->sign(
            $document,
            ($alternateKeys  ? $alternateKeys['public']  : null),
            ($alternateKeys  ? $alternateKeys['private']  : null)
        );

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

    /**
     * Get the current configuration for EngineBlock in it's IdP role from the metadata we received from
     * the Service Registry.
     *
     * @return array Entity configuration
     */
    protected function _getCurrentEntity($serviceName)
    {
        $server = $this->_server;
        $canonicalIdpEntityId = "";
        $this->_withNoVoContext(function() use ($server, $serviceName, &$canonicalIdpEntityId) {
            /** @var $server EngineBlock_Corto_ProxyServer */
            $canonicalIdpEntityId = $server->getUrl($serviceName);
        });

        return $this->_server->getRemoteEntity($canonicalIdpEntityId);
    }

    /**
     * Disable VO context and do something, then reinstate the VO context.
     *
     * @param callable $callbackFn Callback function to execute when no VO Context is set
     */
    protected function _withNoVoContext($callbackFn)
    {
        $voContext = $this->_server->getVirtualOrganisationContext();
        $this->_server->setVirtualOrganisationContext(null);

        $callbackFn();

        $this->_server->setVirtualOrganisationContext($voContext);
    }

    /**
     * Look if a Service Provider EntityId was passed allong (with sp-entity-id) and this entity requires use of
     * different keys (key rollover).
     *
     * @return array|bool
     */
    protected function _getAlternateKeys()
    {
        // Fetch SP Entity Descriptor for the SP Entity ID that is fetched from the request
        $request = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest();
        $spEntityId = $request->getQueryParameter('sp-entity-id');
        if ($spEntityId) {
            $spEntity = $this->_server->getRemoteEntity($spEntityId);

            // Check if an alternative Public key has been set for the requesting SP
            // If yes, use these in the metadata of EngineBlock
            if (isset($spEntity['AlternatePublicKey']) && isset($spEntity['AlternatePrivateKey'])) {
                return array(
                    'public' => $spEntity['AlternatePublicKey'],
                    'private' => $spEntity['AlternatePrivateKey'],
                );
            }
        }
        return false;
    }
}