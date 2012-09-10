<?php

class EngineBlock_Corto_Module_Service_IdpsMetadata extends EngineBlock_Corto_Module_Service_Abstract
{
    const DEFAULT_REQUEST_BINDING  = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect';
    const DEFAULT_RESPONSE_BINDING = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST';

    public function serve()
    {
        $entitiesDescriptor = array(
            EngineBlock_Corto_XmlToArray::TAG_NAME_PFX => 'md:EntitiesDescriptor',
            '_xmlns:md' => 'urn:oasis:names:tc:SAML:2.0:metadata',
            '_xmlns:mdui' => 'urn:oasis:names:tc:SAML:metadata:ui',
            '_ID' => $this->_server->getNewId(),
            'ds:Signature' => '__placeholder__',
            'md:EntityDescriptor' => array()
        );

        // Fetch SP Entity Descriptor for the SP Entity ID that is fetched from the request
        $request = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest();
        $spEntityId = $request->getQueryParameter('sp-entity-id');
        if ($spEntityId) {
            $spEntityDescriptor = $this->_getSpEntityDescriptor($spEntityId);
            $spEntity = $this->_server->getRemoteEntity($spEntityId);
            if ($spEntityDescriptor) {
                $entitiesDescriptor['md:EntityDescriptor'][] = $spEntityDescriptor;
            }
        }

        foreach ($this->_server->getRemoteEntities() as $entityID => $entity) {
            if (!isset($entity['SingleSignOnService'])) continue;

            $entityDescriptor = array(
                '_validUntil' => $this->_server->timeStamp(
                    $this->_server->getCurrentEntitySetting('idpMetadataValidUntilSeconds', 86400)),
                '_entityID' => $entityID,
                'md:IDPSSODescriptor' => array(
                    '_protocolSupportEnumeration' => "urn:oasis:names:tc:SAML:2.0:protocol",
                ));

            if (isset($entity['DisplayName'])) {
                if (!isset($entityDescriptor['md:IDPSSODescriptor']['md:Extensions'])) {
                    $entityDescriptor['md:IDPSSODescriptor']['md:Extensions'] = array();
                }
                if (!isset($entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:UIInfo'])) {
                    $entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:UIInfo'] = array(0=>array());
                }
                foreach ($entity['DisplayName'] as $lang => $name) {
                    if (trim($name)==='') {
                        continue;
                    }
                    if (!isset($entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:UIInfo'][0]['mdui:DisplayName'])) {
                        $entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:UIInfo'][0]['mdui:DisplayName'] = array();
                    }
                    $entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:UIInfo'][0]['mdui:DisplayName'][] = array(
                        '_xml:lang' => $lang,
                        '__v' => $name,
                    );
                }
            }

            if (isset($entity['Description'])) {
                if (!isset($entityDescriptor['md:IDPSSODescriptor']['md:Extensions'])) {
                    $entityDescriptor['md:IDPSSODescriptor']['md:Extensions'] = array();
                }
                if (!isset($entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:UIInfo'])) {
                    $entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:UIInfo'] = array(0=>array());
                }
                foreach ($entity['Description'] as $lang => $name) {
                    if (trim($name)==='') {
                        continue;
                    }
                    if (!isset($entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:UIInfo'][0]['mdui:Description'])) {
                        $entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:UIInfo'][0]['mdui:Description'] = array();
                    }
                    $entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:UIInfo'][0]['mdui:Description'][] = array(
                        '_xml:lang' => $lang,
                        '__v' => $name,
                    );
                }
            }

            $hasLogoHeight = (isset($entity['Logo']['Height']) && $entity['Logo']['Height']);
            $hasLogoWidth  = (isset($entity['Logo']['Width'])  && $entity['Logo']['Width']);
            if (isset($entity['Logo']) && $hasLogoHeight && $hasLogoWidth) {
                if (!isset($entityDescriptor['md:IDPSSODescriptor']['md:Extensions'])) {
                    $entityDescriptor['md:IDPSSODescriptor']['md:Extensions'] = array();
                }
                if (!isset($entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:UIInfo'])) {
                    $entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:UIInfo'] = array(0=>array());
                }
                $entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:UIInfo'][0]['mdui:Logo'] = array(
                    array(
                        '_height' => $entity['Logo']['Height'],
                        '_width'  => $entity['Logo']['Width'],
                        '__v'     => $entity['Logo']['URL'],
                    ),
                );
            }

            if (isset($entity['GeoLocation']) && !empty($entity['GeoLocation'])) {
                if (!isset($entityDescriptor['md:IDPSSODescriptor']['md:Extensions'])) {
                    $entityDescriptor['md:IDPSSODescriptor']['md:Extensions'] = array();
                }
                if (!isset($entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:DiscoHints'])) {
                    $entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:DiscoHints'] = array(0=>array());
                }
                $entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:DiscoHints'][0]['mdui:GeolocationHint'] = array(
                    array(
                        '__v' => $entity['GeoLocation'],
                    ),
                );
            }

            if (isset($entity['Keywords'])) {
                if (!isset($entityDescriptor['md:IDPSSODescriptor']['md:Extensions'])) {
                    $entityDescriptor['md:IDPSSODescriptor']['md:Extensions'] = array();
                }
                if (!isset($entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:UIInfo'])) {
                    $entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:UIInfo'] = array(0=>array());
                }
                $uiInfo = &$entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:UIInfo'][0];
                foreach ($entity['Keywords'] as $lang => $name) {
                    if (trim($name)==='') {
                        continue;
                    }
                    if (!isset($uiInfo['mdui:Keywords'])) {
                        $uiInfo['mdui:Keywords'] = array();
                    }
                    $uiInfo['mdui:Keywords'][] = array(
                        array(
                            '_xml:lang' => $lang,
                            '__v' => $name,
                        ),
                    );
                }
            }

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
                        '_xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                        '_use' => 'signing',
                        'ds:KeyInfo' => array(
                            'ds:X509Data' => array(
                                'ds:X509Certificate' => array(
                                    '__v' => $this->_server->getCertDataFromPem($publicCertificate),
                                ),
                            ),
                        ),
                    ),
                    array(
                        '_xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                        '_use' => 'encryption',
                        'ds:KeyInfo' => array(
                            'ds:X509Data' => array(
                                'ds:X509Certificate' => array(
                                    '__v' => $this->_server->getCertDataFromPem($publicCertificate),
                                ),
                            ),
                        ),
                    ),
                );
            }

            $entityDescriptor['md:IDPSSODescriptor']['md:NameIDFormat'] = array(
                '__v' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient'
            );
            $entityDescriptor['md:IDPSSODescriptor']['md:SingleSignOnService'] = array(
                '_Binding' => self::DEFAULT_REQUEST_BINDING,
                '_Location' => $this->_server->getCurrentEntityUrl('singleSignOnService', $entityID),
            );

            $entitiesDescriptor['md:EntityDescriptor'][] = $entityDescriptor;
        }
        $alternatePublicKey  = isset($spEntity['AlternatePublicKey']) ? $spEntity['AlternatePublicKey'] : null;
        $alternatePrivateKey = isset($spEntity['AlternatePublicKey']) ? $spEntity['AlternatePublicKey'] : null;
        $entitiesDescriptor = $this->_server->sign($entitiesDescriptor, $alternatePublicKey, $alternatePrivateKey);

        $xml = EngineBlock_Corto_XmlToArray::array2xml($entitiesDescriptor);

        $schemaUrl = 'http://docs.oasis-open.org/security/saml/v2.0/saml-schema-metadata-2.0.xsd';
        if ($this->_server->getConfig('debug', false) && ini_get('allow_url_fopen') && file_exists($schemaUrl)) {
            $dom = new DOMDocument();
            $dom->loadXML($xml);
            if (!$dom->schemaValidate($schemaUrl)) {
                echo '<pre>' . htmlentities(EngineBlock_Corto_XmlToArray::formatXml($xml)) . '</pre>';
                throw new Exception('Metadata XML doesnt validate against XSD at Oasis-open.org?!');
            }
        }
        $this->_server->sendHeader('Content-Type', 'application/xml');
        //$this->_server->sendHeader('Content-Type', 'application/samlmetadata+xml');
        $this->_server->sendOutput($xml);
    }

    protected function _getSpEntityDescriptor($spEntityId)
    {
        $entity = $this->_server->getRemoteEntity($spEntityId);
        if (!$entity) {
            return false;
        }

        if (!isset($entity['AssertionConsumerServices'])) {
            return false;
        }

        $entityDescriptor = array(
            '_validUntil' => $this->_server->timeStamp(strtotime('tomorrow') - time()),
            '_entityID' => $spEntityId,
            'md:SPSSODescriptor' => array(
                '_protocolSupportEnumeration' => "urn:oasis:names:tc:SAML:2.0:protocol",
            ),
        );

        if (isset($entity['certificates']['public'])) {
            $entityDescriptor['md:SPSSODescriptor']['md:KeyDescriptor'] = array(
                array(
                    '_xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                    '_use' => 'signing',
                    'ds:KeyInfo' => array(
                        'ds:X509Data' => array(
                            'ds:X509Certificate' => array(
                                '__v' => $this->_server->getCertDataFromPem($entity['certificates']['public']),
                            ),
                        ),
                    ),
                ),
                array(
                    '_xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                    '_use' => 'encryption',
                    'ds:KeyInfo' => array(
                        'ds:X509Data' => array(
                            'ds:X509Certificate' => array(
                                '__v' => $this->_server->getCertDataFromPem($entity['certificates']['public']),
                            ),
                        ),
                    ),
                ),
            );
        }

        $entityDescriptor['md:SPSSODescriptor']['md:NameIDFormat'] = array(
            '__v' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient'
        );
        $entityDescriptor['md:SPSSODescriptor']['md:AssertionConsumerService'] = array(
            '_Binding' => self::DEFAULT_RESPONSE_BINDING,
            '_Location' => $this->_server->getCurrentEntityUrl('assertionConsumerService', $spEntityId),
            '_index' => '1',
        );

        return $entityDescriptor;
    }
}