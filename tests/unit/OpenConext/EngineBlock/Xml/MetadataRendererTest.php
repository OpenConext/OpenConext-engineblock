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

namespace OpenConext\EngineBlock\Xml;

use EngineBlock_Saml2_IdGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Factory\Adapter\IdentityProviderEntity;
use OpenConext\EngineBlock\Metadata\Factory\Adapter\ServiceProviderEntity;
use OpenConext\EngineBlock\Metadata\Factory\Collection\IdentityProviderEntityCollection;
use OpenConext\EngineBlock\Metadata\Factory\Decorator\EngineBlockIdentityProvider;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\RequestedAttribute;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\Utils;
use OpenConext\EngineBlock\Metadata\X509\KeyPairFactory;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use OpenConext\EngineBlock\Metadata\X509\X509PrivateKey;
use OpenConext\EngineBlock\Service\TimeProvider\TimeProvider;
use OpenConext\EngineBlockBundle\Localization\LanguageSupportProvider;
use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecEnc;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\md\EntitiesDescriptor;
use SAML2\XML\md\EntityDescriptor;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Environment;

class MetadataRendererTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var MetadataRenderer
     */
    private $metadataRenderer;

    protected function setUp(): void
    {
        $this->metadataRenderer = $this->buildMetadataRenderer('all');

        parent::setUp();
    }

    /**
     * @test
     * @group Metadata
     */
    public function the_metadata_factory_should_return_valid_signed_xml_for_idp()
    {
        $ssoLocation = 'https://example.com/sso';
        $singleSignOnServices[] = new Service($ssoLocation, Constants::BINDING_HTTP_POST);
        $singleSignOnServices[] = new Service($ssoLocation, Constants::BINDING_HTTP_REDIRECT);

        $idp = Utils::instantiate(
            IdentityProvider::class,
            [
                'entityId' => 'idp',
                'singleSignOnServices' => $singleSignOnServices,
                'logo' => new Logo('https://www.example.com/images/logo.gif'),
            ]
        );

        $idp = new IdentityProviderEntity($idp);

        $xml = $this->metadataRenderer->fromIdentityProviderEntity($idp, 'default');

        // Validate signature and digest
        $this->assertTrue($this->validateXml($xml));

        $dom = DOMDocumentFactory::fromString($xml);

        // Ensure the terms of use comment is present
        $comment = $dom->firstChild;
        $this->assertStringContainsString('trans-openconext_terms_of_use_url', $comment->nodeValue);

        // Assert descriptor

        $entityDescriptor = new EntityDescriptor($dom->childNodes->item(1));
        $this->assertInstanceOf(EntityDescriptor::class, $entityDescriptor);

        // Assert schema
        $this->validateSchema($xml);
    }

    /**
     * @test
     * @group Metadata
     */
    public function the_metadata_factory_should_return_valid_signed_xml_for_sp()
    {
        $assertionConsumerServices[] = new IndexedService(
            'https://example.com/acs',
            Constants::BINDING_HTTP_POST,
            0
        );
        $contactPersons[] = ContactPerson::from(
            'administrative',
            'John',
            'Doe',
            'admin@example.org'
        );

        $sp = Utils::instantiate(
            ServiceProvider::class,
            [
                'entityId' => 'sp',
                'assertionConsumerServices' => $assertionConsumerServices,
                'logo' => new Logo('https://www.example.com/images/logo.gif'),
                'organizationEn' => new Organization('Org', 'Organization', 'https://example.org'),
                'contactPersons' => $contactPersons,
            ]
        );

        $sp = new ServiceProviderEntity($sp);

        $xml = $this->metadataRenderer->fromServiceProviderEntity($sp, 'default');

        // Validate signature and digest
        $this->assertTrue($this->validateXml($xml));

        $dom = DOMDocumentFactory::fromString($xml);

        // Ensure the terms of use comment is present
        $comment = $dom->firstChild;
        $this->assertStringContainsString('trans-openconext_terms_of_use_url', $comment->nodeValue);

        // Assert descriptor
        $entityDescriptor = new EntityDescriptor($dom->childNodes->item(1));
        $this->assertInstanceOf(EntityDescriptor::class, $entityDescriptor);

        // Verify that the requried "mailto:" prefix is present for the EmailAdress tag
        $this->assertStringContainsString('<md:EmailAddress>mailto:admin@example.org</md:EmailAddress>', $xml);

        // Assert schema
        $this->validateSchema($xml);
    }

    /**
     * @test
     * @group Metadata
     */
    public function the_metadata_factory_should_return_valid_signed_xml_for_idps_of_sp()
    {
        $ssoLocation = 'https://example.com/sso';
        $singleSignOnServices[] = new Service($ssoLocation, Constants::BINDING_HTTP_POST);
        $singleSignOnServices[] = new Service($ssoLocation, Constants::BINDING_HTTP_REDIRECT);

        $logo = new Logo('https://www.example.com/logo-url');
        $logo->width = 100;
        $logo->height = 100;

        $idp1 = m::mock(EngineBlockIdentityProvider::class);
        $idp1
            ->shouldReceive('getEntityId')
            ->andReturn('idp1')
            ->shouldReceive('getNameNl', 'getNameEn', 'getDescriptionNl', 'getDescriptionEn')
            ->andReturn('IdP number 1')
            ->shouldReceive('getLogo')
            ->andReturn($logo)
            ->shouldReceive('getSingleSignOnServices')
            ->andReturn($singleSignOnServices)
        ;
        $idp1->shouldIgnoreMissing();

        $idp2 = m::mock(EngineBlockIdentityProvider::class);
        $idp2
            ->shouldReceive('getEntityId')
            ->andReturn('idp2')
            ->shouldReceive('getNameNl', 'getNameEn', 'getDescriptionNl', 'getDescriptionEn')
            ->andReturn('IdP number 2')
            ->shouldReceive('getLogo')
            ->andReturn($logo)
            ->shouldReceive('getSingleSignOnServices')
            ->andReturn($singleSignOnServices)
        ;
        $idp2->shouldIgnoreMissing();

        $collection = new IdentityProviderEntityCollection();
        $collection->add($idp1);
        $collection->add($idp2);

        $xml = $this->metadataRenderer->fromIdentityProviderEntities($collection, 'default');

        // Validate signature and digest
        $this->assertTrue($this->validateXml($xml));

        $dom = DOMDocumentFactory::fromString($xml);

        // Ensure the terms of use comment is present
        $comment = $dom->firstChild;
        $this->assertStringContainsString('trans-openconext_terms_of_use_url', $comment->nodeValue);

        // Assert descriptor
        $entityDescriptor = new EntitiesDescriptor($dom->childNodes->item(1));
        $this->assertInstanceOf(EntitiesDescriptor::class, $entityDescriptor);

        // Assert schema
        $this->validateSchema($xml);
    }

    /**
     * @test
     * @group Metadata
     */
    public function modified_metadata_should_not_pass_signature_validation()
    {
        $assertionConsumerServices[] = new IndexedService(
            'https://example.com/acs',
            Constants::BINDING_HTTP_POST,
            0
        );

        $sp = Utils::instantiate(
            ServiceProvider::class,
            [
                'entityId' => 'sp',
                'assertionConsumerServices' => $assertionConsumerServices,
                'logo' => new Logo('/images/logo.gif'),
                'organizationEn' => new Organization('Org', 'Organization', 'https://example.org'),
            ]
        );

        $sp = new ServiceProviderEntity($sp);

        $xml = $this->metadataRenderer->fromServiceProviderEntity($sp, 'default');
        $xml = str_replace('https://example.com/acs', 'https://example.org/acs', $xml);

        // Validate signature and digest
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('validation failed');
        $this->assertFalse($this->validateXml($xml));
    }

    /**
     * @test
     * @group Metadata
     */
    public function metadata_add_all_requested_attributes()
    {
        $xml = $this->metadataRenderer->fromServiceProviderEntity($this->buildSp(), 'default');

        $this->assertTrue($this->validateXml($xml));
        $this->assertStringContainsString($this->getRequestedAttributeXml('attribute1', true), $xml);
        $this->assertStringContainsString($this->getRequestedAttributeXml('attribute2', false), $xml);
        $this->assertStringContainsString($this->getRequestedAttributeXml('attribute3', false), $xml);
    }

    /**
     * @test
     * @group Metadata
     */
    public function metadata_add_required_requested_attributes()
    {
        $this->metadataRenderer = $this->buildMetadataRenderer('required');

        $xml = $this->metadataRenderer->fromServiceProviderEntity($this->buildSp(), 'default');

        $this->assertTrue($this->validateXml($xml));
        $this->assertStringContainsString($this->getRequestedAttributeXml('attribute1', true), $xml);
        $this->assertStringNotContainsString($this->getRequestedAttributeXml('attribute2', false), $xml);
        $this->assertStringNotContainsString($this->getRequestedAttributeXml('attribute3', false), $xml);
    }

    /**
     * @test
     * @group Metadata
     */
    public function metadata_add_no_requested_attributes()
    {
        $this->metadataRenderer = $this->buildMetadataRenderer('none');

        $xml = $this->metadataRenderer->fromServiceProviderEntity($this->buildSp(), 'default');

        $this->assertTrue($this->validateXml($xml));
        $this->assertStringNotContainsString($this->getRequestedAttributeXml('attribute1', true), $xml);
        $this->assertStringNotContainsString($this->getRequestedAttributeXml('attribute2', false), $xml);
        $this->assertStringNotContainsString($this->getRequestedAttributeXml('attribute3', false), $xml);
    }

    private function buildMetadataRenderer(string $addRequestedAttributes)
    {
        $basePath = realpath(__DIR__ . '/../../../../../');

        $privateKey = new X509PrivateKey($basePath . '/tests/resources/key/engineblock.pem');
        $publicKey = new X509Certificate(openssl_x509_read(file_get_contents($basePath . '/tests/resources/key/engineblock.crt')));
        $keyPair = new X509KeyPair($publicKey, $privateKey);

        $samlIdGenerator = $this->createMock(EngineBlock_Saml2_IdGenerator::class);
        $samlIdGenerator->method('generate')
            ->willReturn('EB_metadata');

        $twigLoader = new \Twig_Loader_Filesystem();
        $twigLoader->addPath($basePath . '/theme/openconext/templates/modules', 'theme');
        $environment = new Environment($twigLoader);

        $translator = m::mock(TranslatorInterface::class);
        $translator
            ->shouldReceive('trans')
            ->andReturnUsing(function($key) {
                return 'trans-'.$key;
            });

        $translatorExtension = new TranslationExtension($translator);
        $environment->addExtension($translatorExtension);

        $keyPairFactory = $this->createMock(KeyPairFactory::class);
        $keyPairFactory
            ->method('buildFromIdentifier')
            ->willReturn($keyPair);

        $documentSigner = new DocumentSigner();

        $languages = ['nl', 'en'];
        $supportedLanguageProvider = new LanguageSupportProvider($languages, $languages);

        return new MetadataRenderer(
            $supportedLanguageProvider,
            $environment,
            $samlIdGenerator,
            $keyPairFactory,
            $documentSigner,
            new TimeProvider(),
            $addRequestedAttributes
        );
    }

    private function buildSp()
    {
        $assertionConsumerServices[] = new IndexedService(
            'https://example.com/acs',
            Constants::BINDING_HTTP_POST,
            0
        );
        $contactPersons[] = ContactPerson::from(
            'administrative',
            'John',
            'Doe',
            'admin@example.org'
        );
        $requestedAttributes = [
            new RequestedAttribute('attribute1', true),
            new RequestedAttribute('attribute2', false),
            new RequestedAttribute('attribute3', false),
        ];

        $sp = Utils::instantiate(
            ServiceProvider::class,
            [
                'entityId' => 'sp',
                'assertionConsumerServices' => $assertionConsumerServices,
                'logo' => new Logo('https://www.example.com/images/logo.gif'),
                'organizationEn' => new Organization('Org', 'Organization', 'https://example.org'),
                'contactPersons' => $contactPersons,
                'requestedAttributes' => $requestedAttributes
            ]
        );

        return new ServiceProviderEntity($sp);
    }

    private function getRequestedAttributeXml(string $attributeName, bool $isRequired)
    {
        return "<md:RequestedAttribute Name=\"$attributeName\" NameFormat=\"urn:oasis:names:tc:SAML:2.0:attrname-format:uri\" " .
            "isRequired=\"" . var_export($isRequired, true) ."\"/>";
    }

    private function validateXml($xml)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $objXMLSecDSig = new XMLSecurityDSig();
        $objXMLSecDSig->idKeys = ['ID'];

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

    private function validateSchema($xml)
    {
        libxml_use_internal_errors(true);

        // Web tests use the dom crawler, if any xml errors are encountered by using the crawler they are stored in the
        // error buffer. Clearing the buffer before validating the schema prevents the showing of irrelevant messages to
        //the end user.
        libxml_clear_errors();

        $doc = new \DOMDocument();
        $doc->loadXml($xml);

        if (!$doc->schemaValidate(__DIR__ . '/schema/surf.xsd')) {
            $errors = libxml_get_errors();
            libxml_clear_errors();

            throw new \Exception(json_encode($errors));
        }
    }

}
