<?php
 
class EngineBlock_Corto_Module_Services extends Corto_Module_Services
{
    public function idPsMetadataService()
    {
        $entitiesDescriptor = array(
            '_xmlns:md' => 'urn:oasis:names:tc:SAML:2.0:metadata',
            '_xmlns:mdui' => 'urn:oasis:names:tc:SAML:2.0:metadata:ui',
            'md:EntityDescriptor' => array()
        );
        foreach ($this->_server->getRemoteEntities() as $entityID => $entity) {
            if (!isset($entity['SingleSignOnService'])) continue;

            $entityDescriptor = array(
                '_validUntil' => $this->_server->timeStamp(strtotime('tomorrow') - time()),
                '_entityID'   => $entityID,
                'md:IDPSSODescriptor' => array(
                    '_protocolSupportEnumeration' => "urn:oasis:names:tc:SAML:2.0:protocol",
            ));

            if (isset($entity['DisplayName'])) {
                if (!isset($entityDescriptor['md:IDPSSODescriptor']['md:Extensions'])) {
                    $entityDescriptor['md:IDPSSODescriptor']['md:Extensions'] = array();
                }
                $entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:DisplayName'] = array();
                foreach ($entity['DisplayName'] as $lang => $name) {
                    $entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:DisplayName'][] = array(
                        '_xml:lang' => $lang,
                        '__v' => $name,
                    );
                }
            }

            if (isset($entity['Logo'])) {
                if (!isset($entityDescriptor['md:IDPSSODescriptor']['md:Extensions'])) {
                    $entityDescriptor['md:IDPSSODescriptor']['md:Extensions'] = array();
                }
                $entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:Logo'] = array(
                    array(
                        '_href'   => $entity['Logo']['Href'],
                        '_height' => $entity['Logo']['Height'],
                        '_width'  => $entity['Logo']['Width'],
                        '__v' => $entity['Logo']['URL'],
                    ),
                );
            }

            if (isset($entity['GeoLocation'])) {
                if (!isset($entityDescriptor['md:IDPSSODescriptor']['md:Extensions'])) {
                    $entityDescriptor['md:IDPSSODescriptor']['md:Extensions'] = array();
                }
                $entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:GeolocationHint'] = array(
                    array(
                        '__v' => $entity['GeoLocation'],
                    ),
                );
            }

            if (isset($entity['certificates']['public'])) {
                $entityDescriptor['md:IDPSSODescriptor']['md:KeyDescriptor'] = array(
                    array(
                        '_xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                        '_use' => 'signing',
                        'ds:KeyInfo' => array(
                            'ds:X509Data' => array(
                                'ds:X509Certificate' => array(
                                    '__v' => self::_getCertDataFromPem($entity['certificates']['public']),
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
                                    '__v' => self::_getCertDataFromPem($entity['certificates']['public']),
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
                '_Binding'  => self::DEFAULT_REQUEST_BINDING,
                '_Location' => $this->_server->getCurrentEntityUrl('singleSignOnService', $entityID),
            );
            
            $entitiesDescriptor['md:EntityDescriptor'][] = $entityDescriptor;
        }

        $request = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest();
        $spEntityId = urldecode($request->getQueryParameter('sp-entity-id'));
        if ($spEntityId) {
            $entityDescriptor = $this->_getSpEntityDescriptor($spEntityId);
            if ($entityDescriptor) {
                $entitiesDescriptor['md:EntityDescriptor'][] = $entityDescriptor;
            }
        }

        $xml = Corto_XmlToArray::array2xml($entitiesDescriptor, 'md:EntitiesDescriptor', true);

        $schemaUrl = 'http://docs.oasis-open.org/security/saml/v2.0/saml-schema-metadata-2.0.xsd';
        if ($this->_server->getConfig('debug', false) && ini_get('allow_url_fopen') && file_exists($schemaUrl)) {
            $dom = new DOMDocument();
            $dom->loadXML($xml);
            if (!$dom->schemaValidate($schemaUrl)) {
                echo '<pre>'.htmlentities(Corto_XmlToArray::formatXml($xml)).'</pre>';
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

        if (!isset($entity['AssertionConsumerService'])) {
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
                                '__v' => self::_getCertDataFromPem($entity['certificates']['public']),
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
                                '__v' => self::_getCertDataFromPem($entity['certificates']['public']),
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
            '_Binding'  => self::DEFAULT_RESPONSE_BINDING,
            '_Location' => $this->_server->getCurrentEntityUrl('assertionConsumerService', $spEntityId),
            '_index'    => '1',
        );

        return $entityDescriptor;
    }

    protected function _getConsentUid($response, $attributes)
    {
        return $response['saml:Assertion']['saml:Subject']['saml:NameID']['__v'];
    }

    /**
     * @return PDO
     */
    protected function _getConsentDatabaseConnection()
    {
        // We only use the write connection because consent is 3 queries of which only 1 light select query.
        $factory = new EngineBlock_Database_ConnectionFactory();
        return $factory->create(EngineBlock_Database_ConnectionFactory::MODE_WRITE);
    }
}
