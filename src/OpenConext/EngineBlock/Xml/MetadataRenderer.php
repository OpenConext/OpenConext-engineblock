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
use Twig\Environment;

class MetadataRenderer
{
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

    /**
     * @var DocumentSigner
     */
    private $documentSigner;

    public function __construct(
        Environment $twig,
        EngineBlock_Saml2_IdGenerator $samlIdGenerator,
        KeyPairFactory $keyPairFactory,
        DocumentSigner $documentSigner
    ) {
        $this->twig = $twig;
        $this->samlIdGenerator = $samlIdGenerator;
        $this->keyPairFactory = $keyPairFactory;
        $this->documentSigner = $documentSigner;
    }

    public function fromServiceProviderEntity(ServiceProviderEntityInterface $sp, string $keyId) : string
    {
        $this->signingKeyPair = $this->keyPairFactory->buildFromIdentifier($keyId);
        $template = '@theme/Authentication/View/Metadata/sp.xml.twig';

        $xml = $this->renderMetadataXmlServiceProvider($sp, $template);
        $signedXml = $this->documentSigner->sign($xml, $this->signingKeyPair);

        return $signedXml;
    }

    public function fromIdentityProviderEntity(IdentityProviderEntityInterface $idp, string $keyId) : string
    {
        $this->signingKeyPair = $this->keyPairFactory->buildFromIdentifier($keyId);
        $template = '@theme/Authentication/View/Metadata/idp.xml.twig';

        $xml = $this->renderMetadataXmlIdentityProvider($idp, $template);
        $signedXml = $this->documentSigner->sign($xml, $this->signingKeyPair);

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

        $signedXml = $this->documentSigner->sign($xml, $this->signingKeyPair);

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
}
