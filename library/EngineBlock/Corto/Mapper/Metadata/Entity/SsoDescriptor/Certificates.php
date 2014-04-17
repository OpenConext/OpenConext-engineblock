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
        if (!isset($this->_entity['certificates'])) {
            $this->_entity['certificates'] = array();
        }

        $entityCertificates = $this->_entity['certificates'];

        if (!empty($this->_entity['AlternatePublicKey'])) {
            $entityCertificates['public'] = $this->_entity['AlternatePublicKey'];
        }

        // No primary certificate to add so no need to even add the KeyDescriptor element.
        if (empty($entityCertificates['public'])) {
            return $rootElement;
        }

        $rootElement['md:KeyDescriptor'] = $this->mapCertsToElement(
            array('public', 'public-fallback', 'public-fallback2'),
            $entityCertificates
        );

        return $rootElement;
    }

    /**
     * Look up the given key names in the entity 'certificates' configuration and if they exist map them as a KeyInfo
     * element to a new root element.
     *
     * Note that duplicate keys are removed as some implementations may not be too happy about duplicate keys,
     * also this enables us to do key rollover because EngineBlock will always give us the 'default' certificate
     * in the first 'public' key and any other certificates in the fallbacks.
     *
     * Scenario here is that first you introduce the new key in certData2 and give SPs an option to upgrade using
     * the keyslug method (key:keyid in the metadata url), this will cause EB to use the new key as 'default' key.
     * Now both default and fallback are the same so metadata with the keyslug will only show the new key.
     *
     * @param array $keyNames
     * @param array $entityCertificates
     * @return array
     */
    public function mapCertsToElement(array $keyNames, array $entityCertificates)
    {
        $element = array();
        $alreadyAdded = array();

        foreach ($keyNames as $keyName) {
            if (empty($entityCertificates[$keyName])) {
                continue;
            }

            $certData = $entityCertificates[$keyName];
            $pem = $this->_mapPem($certData);

            if (in_array($pem, $alreadyAdded)) {
                continue;
            }

            $element[] = $this->getSigningKeyMetadataForCert($pem);

            $alreadyAdded[] = $pem;
        }
        return $element;
    }

    protected function getSigningKeyMetadataForCert($pem)
    {
        return array(
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'use' => 'signing',
            'ds:KeyInfo' => array(
                'ds:X509Data' => array(
                    'ds:X509Certificate' => array(
                        EngineBlock_Corto_XmlToArray::VALUE_PFX => $pem,
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