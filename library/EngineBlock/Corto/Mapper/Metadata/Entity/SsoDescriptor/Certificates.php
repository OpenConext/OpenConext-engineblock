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
        $publicCertificate = $this->_entity['certificates']['public'];

        if (empty($publicCertificate)) {
            return $rootElement;
        }

        $rootElement['md:KeyDescriptor'] = array($this->getSigningKeyMetadataForCert($publicCertificate));

        if (isset($this->_entity['certificates']['public-fallback'])) {
            $rootElement['md:KeyDescriptor'][] = $this->getSigningKeyMetadataForCert(
                $this->_entity['certificates']['public-fallback']
            );
        }

        if (isset($this->_entity['certificates']['public-fallback2'])) {
            $rootElement['md:KeyDescriptor'][] = $this->getSigningKeyMetadataForCert(
                $this->_entity['certificates']['public-fallback2']
            );
        }

        return $rootElement;
    }

    protected function getSigningKeyMetadataForCert($publicCertificate)
    {
        return array(
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'use' => 'signing',
            'ds:KeyInfo' => array(
                'ds:X509Data' => array(
                    'ds:X509Certificate' => array(
                        EngineBlock_Corto_XmlToArray::VALUE_PFX => $this->_mapPem($publicCertificate),
                    ),
                ),
            ),
        );
    }

    protected function _mapPem($pemKey)
    {
        $mapper = new EngineBlock_Corto_Mapper_CertData_Pem($pemKey);
        return $mapper->map();
    }
}