<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

class EngineBlock_Corto_Module_Services extends Corto_Module_Services
{
    protected function _cacheResponse($receivedRequest, $receivedResponse, $type)
    {
        $cachedResponse = &parent::_cacheResponse($receivedRequest, $receivedResponse, $type);
        $cachedResponse['vo'] = $this->_server->getVirtualOrganisationContext();
    }

    protected function _pickCachedResponse(array $cachedResponses, array $request, $requestIssuerEntityId)
    {
        $cachedResponse = parent::_pickCachedResponse($cachedResponses, $request, $requestIssuerEntityId);
        if (!$cachedResponse) {
            return false;
        }

        $this->_server->setVirtualOrganisationContext($cachedResponse['vo']);
        return $cachedResponse;
    }

    public function idPsMetadataService()
    {
        $entitiesDescriptor = array(
            Corto_XmlToArray::TAG_NAME_KEY => 'md:EntitiesDescriptor',
            '_xmlns:md' => 'urn:oasis:names:tc:SAML:2.0:metadata',
            '_xmlns:mdui' => 'urn:oasis:names:tc:SAML:2.0:metadata:ui',
            '_ID' => $this->_server->getNewId(),
            'ds:Signature' => '__placeholder__',
            'md:EntityDescriptor' => array()
        );
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
                        '_href' => $entity['Logo']['Href'],
                        '_height' => $entity['Logo']['Height'],
                        '_width' => $entity['Logo']['Width'],
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

            if (isset($entity['Keywords'])) {
                if (!isset($entityDescriptor['md:IDPSSODescriptor']['md:Extensions'])) {
                    $entityDescriptor['md:IDPSSODescriptor']['md:Extensions'] = array();
                }
                foreach ($entity['Keywords'] as $lang => $name) {
                    $entityDescriptor['md:IDPSSODescriptor']['md:Extensions']['mdui:Keywords'][] = array(
                        array(
                            '_xml:lang' => $lang,
                            '__v' => $name,
                        ),
                    );
                }
            }

            $certificates = $this->_server->getCurrentEntitySetting('certificates', array());
            if (isset($certificates['public'])) {
                $entityDescriptor['md:IDPSSODescriptor']['md:KeyDescriptor'] = array(
                    array(
                        '_xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                        '_use' => 'signing',
                        'ds:KeyInfo' => array(
                            'ds:X509Data' => array(
                                'ds:X509Certificate' => array(
                                    '__v' => $this->_server->getCertDataFromPem($certificates['public']),
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
                                    '__v' => $this->_server->getCertDataFromPem($certificates['public']),
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

        $request = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest();
        $spEntityId = $request->getQueryParameter('sp-entity-id');
        if ($spEntityId) {
            $entityDescriptor = $this->_getSpEntityDescriptor($spEntityId);
            if ($entityDescriptor) {
                $entitiesDescriptor['md:EntityDescriptor'][] = $entityDescriptor;
            }
        }

        $entitiesDescriptor = $this->_server->sign($entitiesDescriptor);

        $xml = Corto_XmlToArray::array2xml($entitiesDescriptor);

        $schemaUrl = 'http://docs.oasis-open.org/security/saml/v2.0/saml-schema-metadata-2.0.xsd';
        if ($this->_server->getConfig('debug', false) && ini_get('allow_url_fopen') && file_exists($schemaUrl)) {
            $dom = new DOMDocument();
            $dom->loadXML($xml);
            if (!$dom->schemaValidate($schemaUrl)) {
                echo '<pre>' . htmlentities(Corto_XmlToArray::formatXml($xml)) . '</pre>';
                throw new Exception('Metadata XML doesnt validate against XSD at Oasis-open.org?!');
            }
        }
        $this->_server->sendHeader('Content-Type', 'application/xml');
        //$this->_server->sendHeader('Content-Type', 'application/samlmetadata+xml');
        $this->_server->sendOutput($xml);
    }

    /**
     * Ask the user for consent over all of the attributes being sent to the SP.
     *
     * Note this is part 1/2 of the Corto Consent Internal Response Processing service.
     *
     * @return void
     */
    public function provideConsentService()
    {
        $response = $this->_server->getBindingsModule()->receiveResponse();
        $_SESSION['consent'][$response['_ID']]['response'] = $response;

        $attributes = Corto_XmlToArray::attributes2array(
            $response['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute']
        );
        $serviceProviderEntityId = $attributes['ServiceProvider'][0];
        unset($attributes['ServiceProvider']);

        $priorConsent = $this->_hasStoredConsent($serviceProviderEntityId, $response, $attributes);
        if ($priorConsent) {
            $response['_Consent'] = 'urn:oasis:names:tc:SAML:2.0:consent:prior';

            $response['_Destination'] = $response['__']['Return'];
            $response['__']['ProtocolBinding'] = self::DEFAULT_RESPONSE_BINDING;

            $this->_server->getBindingsModule()->send(
                $response,
                $this->_server->getRemoteEntity($serviceProviderEntityId)
            );
            return;
        }

        $html = $this->_server->renderTemplate(
            'consent',
            array(
                 'action' => $this->_server->getCurrentEntityUrl('processConsentService'),
                 'ID' => $response['_ID'],
                 'attributes' => $attributes,
                 'sp' => $this->_server->getRemoteEntity($serviceProviderEntityId),
            ));
        $this->_server->sendOutput($html);
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

    protected function _transformIdpsForWayf($idps)
    {
        $wayfIdps = array();
        foreach ($idps as $idp) {
            $remoteEntities = $this->_server->getRemoteEntities();
            $metadata = ($remoteEntities[$idp]);

            $wayfIdp = array(
                'Name_nl' => isset($metadata['Name']['nl']) ? $metadata['Name']['nl'] : 'Geen naam gevonden',
                'Name_en' => isset($metadata['Name']['en']) ? $metadata['Name']['en'] : 'No Name found',
                'Logo' => isset($metadata['Logo']['URL']) ? $metadata['Logo']['URL']
                        : EngineBlock_View::staticUrl() . '/media/idp-logo-not-found.png',
                'Keywords' => isset($metadata['Keywords']['en']) ? explode(' ', $metadata ['Keywords']['en'])
                        : isset($metadata['Keywords']['nl']) ? explode(' ', $metadata['Keywords']['nl']) : 'Undefined',
                'Access' => '1',
                'ID' => md5($idp),
                'EntityId' => $idp,
            );
            $wayfIdps[] = $wayfIdp;
        }

        return $wayfIdps;
    }

    protected function _storeConsent($serviceProviderEntityId, $response, $attributes)
    {
        $parentResponse = parent::_storeConsent($serviceProviderEntityId, $response, $attributes);

        $this->_sendIntroductionMail($response, $attributes);

        return $parentResponse;
    }

    protected function _sendIntroductionMail($response, $attributes)
    {
        $dbh = $this->_getConsentDatabaseConnection();
        $hashedUserId = sha1($this->_getConsentUid($response, $attributes));
        $query = "SELECT COUNT(*) FROM consent where hashed_user_id = ?";
        $parameters = array($hashedUserId);
        $statement = $dbh->prepare($query);
        $statement->execute($parameters);
        $resultSet = $statement->fetchAll();

        //we only send a mail if an user provides consent the first time
        if ($resultSet[0][0] == '1') {
            $mailer = new EngineBlock_Mail_Mailer();
            $mailer->sendMail($attributes,'introduction_email', array());
        }
    }

}
