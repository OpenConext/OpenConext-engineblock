<?php declare(strict_types=1);

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

namespace OpenConext\EngineBlock\Xml;

use DOMDocument;
use EngineBlock_Saml2_IdGenerator;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Factory\Decorator\EngineBlockIdentityProviderMetadata;
use OpenConext\EngineBlock\Metadata\Factory\Decorator\EngineBlockServiceProviderMetadata;
use OpenConext\EngineBlock\Metadata\Factory\IdentityProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Factory\ServiceProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\X509\KeyPairFactory;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use OpenConext\EngineBlock\Xml\ValueObjects\IdentityProviderMetadata;
use OpenConext\EngineBlock\Xml\ValueObjects\IdentityProviderMetadataCollection;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use Twig\Environment;

class MetadataRenderer
{
    const SIGN_ALGORITHM = XMLSecurityDSig::SHA256;
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
    }

    public function fromServiceProviderEntity(ServiceProviderEntityInterface $sp, string $keyId) : string
    {
        $this->signingKeyPair = $this->keyPairFactory->buildFromIdentifier($keyId);
        $template = '@theme/Authentication/View/Metadata/sp.xml.twig';

        $metadata = new EngineBlockServiceProviderMetadata($sp);

        $xml = $this->renderMetadataXmlServiceProvider($metadata, $template);
        $signedXml = $this->signXml($xml, $this->signingKeyPair);

        return $signedXml;
    }

    public function fromIdentityProviderEntity(IdentityProviderEntityInterface $idp, string $keyId) : string
    {
        $this->signingKeyPair = $this->keyPairFactory->buildFromIdentifier($keyId);
        $template = '@theme/Authentication/View/Metadata/idp.xml.twig';

        $metadata = new EngineBlockIdentityProviderMetadata($idp);

        $xml = $this->renderMetadataXmlIdentityProvider($metadata, $template);
        $signedXml = $this->signXml($xml, $this->signingKeyPair);

        return $signedXml;
    }

    /**
     * @param IdentityProvider[] $idps
     * @param string $keyId
     * @return string
     */
    public function fromIdentityProviderEntities(array $idps, string $keyId) : string
    {
        $this->signingKeyPair = $this->keyPairFactory->buildFromIdentifier($keyId);
        $template = '@theme/Authentication/View/Metadata/idps.xml.twig';

        $metadata = new IdentityProviderMetadataCollection();
        foreach ($idps as $role) {
            $metadata->add(new IdentityProviderMetadata($role));
        }

        $xml = $this->renderMetadataXmlIdentityProviderCollection($metadata, $template);

        $signedXml = $this->signXml($xml, $this->signingKeyPair);

        return $signedXml;
    }

    private function renderMetadataXmlServiceProvider(EngineBlockServiceProviderMetadata $metadata, string $template) : string
    {
        $params = [
            'id' => $this->samlIdGenerator->generate(self::ID_PREFIX, EngineBlock_Saml2_IdGenerator::ID_USAGE_SAML2_METADATA),
            'metadata' => $metadata,
        ];

        return $this->twig->render($template, $params);
    }

    private function renderMetadataXmlIdentityProvider(EngineBlockIdentityProviderMetadata $metadata, string $template) : string
    {
        $params = [
            'id' => $this->samlIdGenerator->generate(self::ID_PREFIX, EngineBlock_Saml2_IdGenerator::ID_USAGE_SAML2_METADATA),
            'metadata' => $metadata,
        ];

        return $this->twig->render($template, $params);
    }

    private function renderMetadataXmlIdentityProviderCollection(IdentityProviderMetadataCollection $metadataCollection, string $template) : string
    {
        $params = [
            'id' => $this->samlIdGenerator->generate(self::ID_PREFIX, EngineBlock_Saml2_IdGenerator::ID_USAGE_SAML2_METADATA),
            'metadataCollection' => $metadataCollection,
        ];

        return $this->twig->render($template, $params);
    }

    private function signXml(string $source, X509KeyPair $signingKeyPair) : string
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
        $objDSig->insertSignature($doc->documentElement, $doc->documentElement->firstChild);

        // Save the signed XML
        return $doc->saveXML();
    }
}
