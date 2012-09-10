<?php

class EngineBlock_Corto_Module_Service_IdpMetadata extends EngineBlock_Corto_Module_Service_Abstract
{
    public function serve()
    {
        // Fetch SP Entity Descriptor for the SP Entity ID that is fetched from the request
        $request = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest();
        $spEntityId = $request->getQueryParameter('sp-entity-id');
        if ($spEntityId) {
            $spEntity = $this->_server->getRemoteEntity($spEntityId);
        }

        $entityDescriptor = array(
            EngineBlock_Corto_XmlToArray::TAG_NAME_PFX => 'md:EntityDescriptor',
            EngineBlock_Corto_XmlToArray::COMMENT_PFX => self::META_TOU_COMMENT,
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:md' => 'urn:oasis:names:tc:SAML:2.0:metadata',
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:mdui' => 'urn:oasis:names:tc:SAML:metadata:ui',
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'validUntil' => $this->_server->timeStamp($this->_server->getCurrentEntitySetting(
                'idpMetadataValidUntilSeconds', 86400)),
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'entityID' => $this->_server->getCurrentEntityUrl('idPMetadataService'),
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID' => $this->_server->getNewId(),
            'ds:Signature' => EngineBlock_Corto_XmlToArray::PLACEHOLDER_VALUE,
            'md:IDPSSODescriptor' => array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'protocolSupportEnumeration' => "urn:oasis:names:tc:SAML:2.0:protocol",
            ),
        );

        $voContext = $this->_server->getVirtualOrganisationContext();
        $this->_server->setVirtualOrganisationContext(null);
        $canonicalIdpEntityId = $this->_server->getCurrentEntityUrl('idPMetadataService');
        $this->_server->setVirtualOrganisationContext($voContext);
        $entityDetails = $this->_server->getRemoteEntity($canonicalIdpEntityId);

        $this->_addContactPersonsToEntityDescriptor($entityDescriptor, $entityDetails);

        $this->_addDisplayNamesToEntityDescriptor($entityDescriptor['md:IDPSSODescriptor'], $entityDetails);

        $this->_addDescriptionToEntityDescriptor($entityDescriptor['md:IDPSSODescriptor'], $entityDetails);

        // Check if an alternative Public & Private key have been set for a SP
        // If yes, use these in the metadata of Engineblock
        if (isset($spEntity)
            && $spEntity['AlternatePrivateKey']
            && $spEntity['AlternatePublicKey']
        ) {
            $publicCertificate = $spEntity['AlternatePublicKey'];
        } else {
            $certificates = $this->_server->getCurrentEntitySetting('certificates', array());
            $publicCertificate = $certificates['public'];
        }

        if (isset($publicCertificate)) {
            $entityDescriptor['md:IDPSSODescriptor']['md:KeyDescriptor'] = array(
                array(
                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'use' => 'signing',
                    'ds:KeyInfo' => array(
                        'ds:X509Data' => array(
                            'ds:X509Certificate' => array(
                                EngineBlock_Corto_XmlToArray::VALUE_PFX => $this->_server->getCertDataFromPem($publicCertificate),
                            ),
                        ),
                    ),
                ),
                array(
                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'use' => 'encryption',
                    'ds:KeyInfo' => array(
                        'ds:X509Data' => array(
                            'ds:X509Certificate' => array(
                                EngineBlock_Corto_XmlToArray::VALUE_PFX => $this->_server->getCertDataFromPem($publicCertificate),
                            ),
                        ),
                    ),
                    'md:EncryptionMethod' => array(
                        array(
                            '_Algorithm' => 'http://www.w3.org/2001/04/xmlenc#rsa-1_5',
                        ),
                    ),
                ),
            );
        }
        $entityDescriptor['md:IDPSSODescriptor']['md:NameIDFormat'] = array(
            array(EngineBlock_Corto_XmlToArray::VALUE_PFX => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent'),
            array(EngineBlock_Corto_XmlToArray::VALUE_PFX => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient'),
            array(EngineBlock_Corto_XmlToArray::VALUE_PFX => 'urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified'),
        );
        $entityDescriptor['md:IDPSSODescriptor']['md:SingleSignOnService'] = array(
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Binding'  => self::DEFAULT_REQUEST_BINDING,
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Location' => $this->_server->getCurrentEntityUrl('singleSignOnService'),
        );

        $entityDescriptor = $this->_server->sign(
            $entityDescriptor,
            (isset($spEntity['AlternatePublicKey'])  ? $spEntity['AlternatePublicKey']  : null),
            (isset($spEntity['AlternatePrivateKey']) ? $spEntity['AlternatePrivateKey'] : null)
        );
        $xml = EngineBlock_Corto_XmlToArray::array2xml($entityDescriptor);

        $this->_validateXml($xml);

        $this->_server->sendHeader('Content-Type', 'application/xml');
        //$this->_server->sendHeader('Content-Type', 'application/samlmetadata+xml');
        $this->_server->sendOutput($xml);
    }
}