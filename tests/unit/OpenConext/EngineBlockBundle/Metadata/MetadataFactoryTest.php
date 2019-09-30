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

use EngineBlock_Saml2_IdGenerator;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Utils;
use OpenConext\EngineBlock\Metadata\X509\KeyPairFactory;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use OpenConext\EngineBlock\Metadata\X509\X509PrivateKey;
use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecEnc;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SAML2\DOMDocumentFactory;
use SAML2\XML\md\EntityDescriptor;
use Twig\Environment;

class MetadataFactoryTest extends TestCase
{
    /**
     * @var MetadataFactory
     */
    private $metadataFactory;

    protected function setUp()
    {
        $basePath = realpath(__DIR__ . '/../../../../../');

        $privateKey = new X509PrivateKey($basePath . '/tests/resources/key/engineblock.pem');
        $publicKey = new X509Certificate(openssl_x509_read(file_get_contents($basePath . '/tests/resources/key/engineblock.crt')));
        $keyPair = new X509KeyPair($publicKey, $privateKey);

        $samlIdGenerator = $this->createMock(EngineBlock_Saml2_IdGenerator::class);
        $samlIdGenerator->method('generate')
            ->willReturn('EB_metadata');

        $twigLoader = new \Twig_Loader_Filesystem();
        $twigLoader->addPath($basePath . '/theme/material/templates/modules', 'theme');
        $environment = new Environment($twigLoader);

        $keyPairFactory = $this->createMock(KeyPairFactory::class);
        $keyPairFactory
            ->method('buildFromIdentifier')
            ->willReturn($keyPair);

        $this->metadataFactory = new MetadataFactory($environment, $samlIdGenerator, $keyPairFactory);

        parent::setUp();
    }

    /**
     * @test
     * @group Metadata
     */
    public function the_metadata_factory_should_return_valid_signed_xml_for_idp()
    {
        $idp = Utils::instantiate(
            IdentityProvider::class,
            [
                'entityId' => 'idp',
            ]
        );

        $xml = $this->metadataFactory->fromIdentityProviderEntity($idp, 'default');

        // Validate signature and digest
        $this->assertTrue($this->validateXml($xml));

        // Assert descriptor
        $dom = DOMDocumentFactory::fromString($xml);
        $entityDescriptor = new EntityDescriptor($dom->firstChild);
        $this->assertInstanceOf(EntityDescriptor::class, $entityDescriptor);
    }

    /**
     * @test
     * @group Metadata
     */
    public function the_metadata_factory_should_return_valid_signed_xml_for_sp()
    {
        $sp = Utils::instantiate(
            ServiceProvider::class,
            [
                'entityId' => 'sp',
            ]
        );

        $xml = $this->metadataFactory->fromServiceProviderEntity($sp, 'default');

        // Validate signature and digest
        $this->assertTrue($this->validateXml($xml));

        // Assert descriptor
        $dom = DOMDocumentFactory::fromString($xml);
        $entityDescriptor = new EntityDescriptor($dom->firstChild);
        $this->assertInstanceOf(EntityDescriptor::class, $entityDescriptor);
    }

    private function validateXml($xml)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $objXMLSecDSig = new XMLSecurityDSig();

        $objDSig = $objXMLSecDSig->locateSignature($doc);
        if (!$objDSig) {
            throw new \Exception('Unable to find signature');
        }
        $objXMLSecDSig->canonicalizeSignedInfo();

        if (!$objXMLSecDSig->validateReference()) {
            throw new \Exception('Reference validation failed');
        }

        $objKey = $objXMLSecDSig->locateKey();
        $objKeyInfo = XMLSecEnc::staticLocateKeyInfo($objKey, $objDSig);

        if (!$objXMLSecDSig->verify($objKey)) {
            throw new \Exception('Signature validation failed');
        }

        return true;
    }

}
