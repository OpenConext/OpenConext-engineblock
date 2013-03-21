<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_Certificates
{
    private $_entity;

    public function __construct($entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        if (!empty($this->_entity['AlternatePublicKey'])) {
            $publicCertificate = $this->_entity['AlternatePublicKey'];
        } else {
            $publicCertificate = $this->_entity['certificates']['public'];
        }

        if (empty($publicCertificate)) {
            return $rootElement;
        }
        $rootElement['md:KeyDescriptor'] = array(
            array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'use' => 'signing',
                'ds:KeyInfo' => array(
                    'ds:X509Data' => array(
                        'ds:X509Certificate' => array(
                            EngineBlock_Corto_XmlToArray::VALUE_PFX => $this->_mapPem($publicCertificate),
                        ),
                    ),
                ),
            ),
/**
 * https://jira.surfconext.nl/jira/browse/BACKLOG-874
 *
 * Encryption key is no longer provided to prevent the idp returning an encrypted response

            array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'use' => 'encryption',
                'ds:KeyInfo' => array(
                    'ds:X509Data' => array(
                        'ds:X509Certificate' => array(
                            EngineBlock_Corto_XmlToArray::VALUE_PFX => $this->_mapPem($publicCertificate),
                        ),
                    ),
                ),
                'md:EncryptionMethod' => array(
                    array(
                        EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Algorithm' => 'http://www.w3.org/2001/04/xmlenc#rsa-1_5',
                    ),
                ),
            ),
*/
        );
        return $rootElement;
    }

    protected function _mapPem($pemKey)
    {
        $mapper = new EngineBlock_Corto_Mapper_CertData_Pem($pemKey);
        return $mapper->map();
    }
}