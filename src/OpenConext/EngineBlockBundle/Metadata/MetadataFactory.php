<?php
/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlockBundle\Metadata;

use DOMDocument;
use EngineBlock_Saml2_IdGenerator;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\X509\KeyPairFactory;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use Twig\Environment;

class MetadataFactory
{
    const SIGN_ALGORITHM = XMLSecurityDSig::SHA1;
    const ID_PREFIX = 'EB';

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var EngineBlock_Saml2_IdGenerator
     */
    private $samlIdGenerator;

    /**
     * @var X509KeyPair
     */
    private $signingKeyPair;

    /**
     * @var KeyPairFactory
     */
    private $keyPairFactory;

    public function __construct(Environment $twig, EngineBlock_Saml2_IdGenerator $samlIdGenerator, KeyPairFactory $keyPairFactory)
    {
        $this->twig = $twig;
        $this->samlIdGenerator = $samlIdGenerator;
        $this->keyPairFactory = $keyPairFactory;

        // Use the default configured key as default
        $this->signingKeyPair = $keyPairFactory->buildFromIdentifier();
    }

    public function fromServiceProviderEntity(ServiceProvider $role)
    {
        $template = '@theme/Authentication/View/Metadata/sp.html.twig';

        $xml = $this->renderMetadataXml($role, $template);
        $signedXml = $this->signXml($xml, $this->signingKeyPair);

        return $signedXml;
    }

    public function fromIdentityProviderEntity(IdentityProvider $role)
    {
        $template = '@theme/Authentication/View/Metadata/idp.html.twig';

        $xml = $this->renderMetadataXml($role, $template);
        $signedXml = $this->signXml($xml, $this->signingKeyPair);

        return $signedXml;
    }

    public function setKey(string $keyId)
    {
        $this->signingKeyPair = $this->keyPairFactory->buildFromIdentifier($keyId);
    }

    private function renderMetadataXml(AbstractRole $role, $template)
    {
        $params = [
            'id' => $this->samlIdGenerator->generate(self::ID_PREFIX, EngineBlock_Saml2_IdGenerator::ID_USAGE_SAML2_METADATA),
            'entity' => $role,
            'publicKey' => $this->signingKeyPair->getCertificate()->toCertData()
        ];

        return $this->twig->render($template, $params);
    }

    private function signXml($source, X509KeyPair $signingKeyPair)
    {
        // Load the XML to be signed
        $doc = new DOMDocument();
        $doc->loadXML($source);

        // Create sign object
        $objDSig = new XMLSecurityDSig();
        $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
        $objDSig->addReference(
            $doc,
            self::SIGN_ALGORITHM,
            array('http://www.w3.org/2000/09/xmldsig#enveloped-signature')
        );

        // Load private key
        $objKey = $signingKeyPair->getPrivateKey()->toXmlSecurityKey();
        $objKey->loadKey($signingKeyPair->getPrivateKey()->getFilePath(), true);

        // Sign with private key
        $objDSig->sign($objKey);

        // Add the associated public key to the signature
        $objDSig->add509Cert($signingKeyPair->getCertificate()->toPem());

        // Append the signature to the XML
        $objDSig->appendSignature($doc->documentElement);

        // Save the signed XML
        return $doc->saveXML();
    }
}