<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractConfigurationEntity;

class EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_Certificates
{
    /**
     * @var AbstractConfigurationEntity
     */
    private $_entity;

    /**
     * @param AbstractConfigurationEntity $entity
     */
    public function __construct($entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        /** @var EngineBlock_X509_Certificate[] $certificates */
        $certificates = $this->_entity->certificates;

        // No primary certificate to add so no need to even add the KeyDescriptor element.
        if (empty($certificates)) {
            return $rootElement;
        }

        $rootElement['md:KeyDescriptor'] = $this->mapCertificatesToElement(
            $certificates
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
     * @param EngineBlock_X509_Certificate[] $certificates
     * @return array
     */
    public function mapCertificatesToElement(array $certificates)
    {
        $element = array();
        $alreadyAdded = array();

        foreach ($certificates as $certificate) {
            $certData = $certificate->toCertData();

            if (in_array($certData, $alreadyAdded)) {
                continue;
            }

            $element[] = $this->getSigningKeyMetadataForCert($certData);

            $alreadyAdded[] = $certData;
        }
        return $element;
    }

    protected function getSigningKeyMetadataForCert($certData)
    {
        return array(
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'use' => 'signing',
            'ds:KeyInfo' => array(
                'ds:X509Data' => array(
                    'ds:X509Certificate' => array(
                        EngineBlock_Corto_XmlToArray::VALUE_PFX => $certData,
                    ),
                ),
            ),
        );
    }
}