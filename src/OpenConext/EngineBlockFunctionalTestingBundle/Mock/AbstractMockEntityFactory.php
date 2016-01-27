<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Mock;

/**
 * Class AbstractMockEntityFactory
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Mock
 */
abstract class AbstractMockEntityFactory
{
    /**
     * @return \SAML2_XML_md_KeyDescriptor
     */
    protected function generateDefaultSigningKeyPair()
    {
        $signingKey = new \SAML2_XML_md_KeyDescriptor();
        $signingKey->use = 'signing';

        $keyInfo = new \SAML2_XML_ds_KeyInfo();
        $keyInfo->Id = "CONEXT-ETS-KEY-SNAKEOIL";

        $keyName = new \SAML2_XML_ds_KeyName();
        $keyName->name = "snakeoil";

        $x509Data = new \SAML2_XML_ds_X509Data();

        $certificate = new \SAML2_XML_ds_X509Certificate();
        $certificate->certificate = trim(file_get_contents(__DIR__ . '/../Resources/keys/snakeoil.certData'));

        $domElement = new \DOMElement('PrivateKey');
        $domElement->nodeValue = trim(file_get_contents(__DIR__ . '/../Resources/keys/snakeoil.key'));

        $document = new \DOMDocument();
        $document->appendChild($domElement);
        $privateKeyChunk = new \SAML2_XML_Chunk($domElement);

        $x509Data->data[] = $certificate;
        $keyInfo->info[] = $keyName;
        $keyInfo->info[] = $x509Data;
        $keyInfo->info[] = $privateKeyChunk;
        $signingKey->KeyInfo = $keyInfo;

        return $signingKey;
    }
}
