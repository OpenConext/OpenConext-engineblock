<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlock\Metadata\Value\X509\Certificate;
use OpenConext\EngineBlock\Metadata\Value\X509\CertificateList;
use OpenConext\Value\Saml\Metadata\Common\Binding;
use OpenConext\Value\Saml\Metadata\Common\Endpoint;
use OpenConext\Value\Saml\Metadata\Common\IndexedEndpoint;
use OpenConext\Value\Saml\Metadata\ContactPerson;
use OpenConext\Value\Saml\Metadata\ContactPerson\Company;
use OpenConext\Value\Saml\Metadata\ContactPerson\ContactType;
use OpenConext\Value\Saml\Metadata\ContactPerson\EmailAddress;
use OpenConext\Value\Saml\Metadata\ContactPerson\EmailAddressList;
use OpenConext\Value\Saml\Metadata\ContactPerson\GivenName;
use OpenConext\Value\Saml\Metadata\ContactPerson\Surname;
use OpenConext\Value\Saml\Metadata\ContactPerson\TelephoneNumber;
use OpenConext\Value\Saml\Metadata\ContactPerson\TelephoneNumberList;
use OpenConext\Value\Saml\Metadata\ContactPersonList;
use OpenConext\Value\Saml\Metadata\Organization;
use OpenConext\Value\Saml\Metadata\Organization\OrganizationDisplayName;
use OpenConext\Value\Saml\Metadata\Organization\OrganizationDisplayNameList;
use OpenConext\Value\Saml\Metadata\Organization\OrganizationName;
use OpenConext\Value\Saml\Metadata\Organization\OrganizationNameList;
use OpenConext\Value\Saml\Metadata\Organization\OrganizationUrl;
use OpenConext\Value\Saml\Metadata\Organization\OrganizationUrlList;
use OpenConext\Value\Saml\NameIdFormat;
use OpenConext\Value\Saml\NameIdFormatList;
use PHPUnit_Framework_TestCase as UnitTest;

class ServiceProviderSamlConfigurationTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function entity_saml_configuration_can_be_retrieved()
    {
        $entitySamlConfiguration = $this->getEntitySamlConfiguration();
        $samlConfiguration = new ServiceProviderSamlConfiguration(
            $entitySamlConfiguration,
            new AssertionConsumerServices([new IndexedEndpoint(new Endpoint(Binding::httpPost(), 'uri'), 1)])
        );

        $this->assertSame($entitySamlConfiguration, $samlConfiguration->getEntitySamlConfiguration());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function assertion_consumer_services_can_be_retrieved()
    {
        $assertionConsumerServices        = new AssertionConsumerServices(
            [new IndexedEndpoint(new Endpoint(Binding::httpPost(), 'uri'), 1)]
        );
        $samlConfiguration = new ServiceProviderSamlConfiguration(
            $this->getEntitySamlConfiguration(),
            $assertionConsumerServices
        );

        $this->assertSame($assertionConsumerServices, $samlConfiguration->getAssertionConsumerServices());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function equality_is_verified_on_all_properties()
    {
        $base = new ServiceProviderSamlConfiguration(
            $this->getEntitySamlConfiguration(),
            new AssertionConsumerServices([new IndexedEndpoint(new Endpoint(Binding::httpPost(), 'uri'), 1)])
        );
        $same = new ServiceProviderSamlConfiguration(
            $this->getEntitySamlConfiguration(),
            new AssertionConsumerServices([new IndexedEndpoint(new Endpoint(Binding::httpPost(), 'uri'), 1)])
        );
        $differentSamlConfiguration = new ServiceProviderSamlConfiguration(
            $this->getDifferentEntitySamlConfiguration(),
            new AssertionConsumerServices([new IndexedEndpoint(new Endpoint(Binding::httpPost(), 'uri'), 1)])
        );
        $differentAssertionConsumerServices = new ServiceProviderSamlConfiguration(
            $this->getEntitySamlConfiguration(),
            new AssertionConsumerServices([])
        );

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($differentSamlConfiguration));
        $this->assertFalse($base->equals($differentAssertionConsumerServices));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_a_serialized_service_provider_saml_configuration()
    {
        $original = new ServiceProviderSamlConfiguration(
            $this->getEntitySamlConfiguration(),
            new AssertionConsumerServices([new IndexedEndpoint(new Endpoint(Binding::httpPost(), 'uri'), 1)])
        );

        $deserialized = ServiceProviderSamlConfiguration::deserialize($original->serialize());

        $this->assertTrue($original->equals($deserialized));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     * @dataProvider \OpenConext\TestDataProvider::notArray
     *
     * @param mixed $notArray
     */
    public function deserialization_requires_data_to_be_an_array($notArray)
    {
        $this->expectException(InvalidArgumentException::class);

        ServiceProviderSamlConfiguration::deserialize($notArray);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     * @dataProvider invalidDataProvider
     *
     * @param array $invalidData
     */
    public function deserialization_enforces_the_presence_of_all_required_keys($invalidData)
    {
        $this->expectException(InvalidArgumentException::class);

        ServiceProviderSamlConfiguration::deserialize($invalidData);
    }

    public function invalidDataProvider()
    {
        return [
            'no matches' => [
                [
                    'foo' => (new AssertionConsumerServices([]))->serialize(),
                    'bar' => $this->getEntitySamlConfiguration()->serialize()
                ]
            ],
            'no entity_saml_configuration' => [
                ['assertion_consumer_services' => (new AssertionConsumerServices([]))->serialize()]
            ],
            'no assertion_consumer_services' => [
                ['entity_saml_configuration' => $this->getEntitySamlConfiguration()->serialize()]
            ]
        ];
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function service_provider_saml_configuration_can_be_cast_to_string()
    {
        $serviceProviderSamlConfiguration = new ServiceProviderSamlConfiguration(
            $this->getEntitySamlConfiguration(),
            new AssertionConsumerServices([new IndexedEndpoint(new Endpoint(Binding::httpPost(), 'uri'), 1)])
        );

        $this->assertInternalType('string', (string) $serviceProviderSamlConfiguration);
    }

    /**
     * Helper method for instantiating an EntitiySamlConfiguration
     *
     * @return EntitySamlConfiguration
     */
    private function getEntitySamlConfiguration()
    {
        return new EntitySamlConfiguration(
            NameIdFormat::entity(),
            new NameIdFormatList([NameIdFormat::entity(), NameIdFormat::persistent()]),
            new CertificateList([new Certificate('foo')]),
            new Endpoint(Binding::httpRedirect(), 'some:uri'),
            new Endpoint(Binding::httpPost(), 'some:other:uri'),
            new ContactPersonList(
                [
                    new ContactPerson(
                        ContactType::administrative(),
                        new EmailAddressList([new EmailAddress('john.doe@domain.invalid')]),
                        new TelephoneNumberList([new TelephoneNumber('00-0-00000000')]),
                        new GivenName('John'),
                        new Surname('Doe'),
                        new Company('OpenConext')
                    )
                ]
            ),
            new Organization(
                new OrganizationNameList([new OrganizationName('OpenConext', 'nl')]),
                new OrganizationDisplayNameList([new OrganizationDisplayName('OpenConext', 'nl')]),
                new OrganizationUrlList([new OrganizationUrl('https://www.openconext.org', 'nl')])
            )
        );
    }

    /**
     * Helper method for instantiating an EntitiySamlConfiguration
     *
     * @return EntitySamlConfiguration
     */
    private function getDifferentEntitySamlConfiguration()
    {
        return new EntitySamlConfiguration(
            NameIdFormat::entity(),
            new NameIdFormatList([]),
            new CertificateList([]),
            new Endpoint(Binding::httpRedirect(), 'some:uri'),
            new Endpoint(Binding::httpPost(), 'some:other:uri'),
            new ContactPersonList([])
        );
    }
}
