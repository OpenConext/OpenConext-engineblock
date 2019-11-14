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
use OpenConext\EngineBlock\Metadata\Factory\Collection\IdentityProviderEntityCollection;
use OpenConext\EngineBlock\Metadata\Factory\Helper\IdentityProviderMetadataHelper;
use OpenConext\EngineBlock\Metadata\Factory\Helper\ServiceProviderMetadataHelper;
use OpenConext\EngineBlock\Metadata\Factory\IdentityProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Factory\ServiceProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\X509\KeyPairFactory;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use OpenConext\EngineBlock\Service\TimeProvider\TimeProvider;
use Twig\Environment;

class MetadataRenderer
{
    const ID_PREFIX = 'EB';

    /**
     * The number of seconds a Metadata document is deemed valid
     */
    const METADATA_EXPIRATION_TIME = 86400;

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

    /**
     * @var string
     */
    private $termsOfUse;
    /**
     * @var TimeProvider
     */
    private $timeProvider;

    public function __construct(
        Environment $twig,
        EngineBlock_Saml2_IdGenerator $samlIdGenerator,
        KeyPairFactory $keyPairFactory,
        DocumentSigner $documentSigner,
        TimeProvider $timeProvider,
        string $termsOfUse
    ) {
        $this->twig = $twig;
        $this->samlIdGenerator = $samlIdGenerator;
        $this->keyPairFactory = $keyPairFactory;
        $this->documentSigner = $documentSigner;
        $this->timeProvider = $timeProvider;
        $this->termsOfUse = $termsOfUse;
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

    public function fromIdentityProviderEntities(IdentityProviderEntityCollection $idps, string $keyId) : string
    {
        $this->signingKeyPair = $this->keyPairFactory->buildFromIdentifier($keyId);
        $template = '@theme/Authentication/View/Metadata/idps.xml.twig';

        $xml = $this->renderMetadataXmlIdentityProviderCollection($idps, $template);

        $signedXml = $this->documentSigner->sign($xml, $this->signingKeyPair);

        return $signedXml;
    }

    private function renderMetadataXmlServiceProvider(ServiceProviderEntityInterface $sp, string $template) : string
    {
        $metadata = new ServiceProviderMetadataHelper($sp);

        $params = [
            'id' => $this->samlIdGenerator->generate(self::ID_PREFIX, EngineBlock_Saml2_IdGenerator::ID_USAGE_SAML2_METADATA),
            'validUntil' => $this->getValidUntil(),
            'metadata' => $metadata,
            'termsOfUse' => $this->termsOfUse,
        ];

        return $this->twig->render($template, $params);
    }

    private function renderMetadataXmlIdentityProvider(IdentityProviderEntityInterface $idp, string $template) : string
    {
        $metadata = new IdentityProviderMetadataHelper($idp);

        $params = [
            'id' => $this->samlIdGenerator->generate(self::ID_PREFIX, EngineBlock_Saml2_IdGenerator::ID_USAGE_SAML2_METADATA),
            'validUntil' => $this->getValidUntil(),
            'metadata' => $metadata,
            'termsOfUse' => $this->termsOfUse,
        ];

        return $this->twig->render($template, $params);
    }

    private function renderMetadataXmlIdentityProviderCollection(IdentityProviderEntityCollection $idpCollection, string $template) : string
    {
        $metadataCollection = new IdentityProviderEntityCollection();
        foreach ($idpCollection as $idp) {
            $metadataCollection->add(new IdentityProviderMetadataHelper($idp));
        }

        $params = [
            'id' => $this->samlIdGenerator->generate(self::ID_PREFIX, EngineBlock_Saml2_IdGenerator::ID_USAGE_SAML2_METADATA),
            'validUntil' => $this->getValidUntil(),
            'metadataCollection' => $metadataCollection,
            'termsOfUse' => $this->termsOfUse,
        ];

        return $this->twig->render($template, $params);
    }

    private function getValidUntil(): string
    {
        return $this->timeProvider->timestamp(self::METADATA_EXPIRATION_TIME);
    }
}
