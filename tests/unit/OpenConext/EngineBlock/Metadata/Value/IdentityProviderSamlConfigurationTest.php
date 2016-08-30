<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlock\Metadata\Value\X509\Certificate;
use OpenConext\EngineBlock\Metadata\Value\X509\CertificateList;
use OpenConext\Value\Saml\Metadata\Common\Binding;
use OpenConext\Value\Saml\Metadata\Common\Endpoint;
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
use OpenConext\Value\Saml\Metadata\ShibbolethMetadataScope;
use OpenConext\Value\Saml\Metadata\ShibbolethMetadataScopeList;
use OpenConext\Value\Saml\NameIdFormat;
use OpenConext\Value\Saml\NameIdFormatList;
use PHPUnit_Framework_TestCase as UnitTest;

class IdentityProviderSamlConfigurationTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_entity_saml_configuration_can_be_retrieved()
    {
        $entitySamlConfiguration = $this->getDefaultEntitySamlConfiguration();
        $identityProviderSamlConfiguration = new IdentityProviderSamlConfiguration(
            $entitySamlConfiguration,
            new SingleSignOnServices([]),
            new ShibbolethMetadataScopeList([])
        );

        $this->assertSame($entitySamlConfiguration, $identityProviderSamlConfiguration->getEntitySamlConfiguration());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_single_sign_on_services_can_be_retrieved()
    {
        $singleSignOnServices = new SingleSignOnServices([new Endpoint(Binding::httpPost(), 'some:uri')]);
        $identityProviderSamlConfiguration = new IdentityProviderSamlConfiguration(
            $this->getDefaultEntitySamlConfiguration(),
            $singleSignOnServices,
            new ShibbolethMetadataScopeList([])
        );

        $this->assertSame($singleSignOnServices, $identityProviderSamlConfiguration->getSingleSignOnServices());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_shibboleth_metadata_scope_can_be_retrieved()
    {
        $shibbolethMetadataScopeList = new ShibbolethMetadataScopeList([new ShibbolethMetadataScope('OpenConext.org')]);
        $identityProviderSamlConfiguration = new IdentityProviderSamlConfiguration(
            $this->getDefaultEntitySamlConfiguration(),
            new SingleSignOnServices([]),
            $shibbolethMetadataScopeList
        );

        $this->assertSame(
            $shibbolethMetadataScopeList,
            $identityProviderSamlConfiguration->getShibbolethMetadataScopeList()
        );
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function equality_is_verified_on_all_properties()
    {
        $base = new IdentityProviderSamlConfiguration(
            $this->getDefaultEntitySamlConfiguration(),
            new SingleSignOnServices([]),
            new ShibbolethMetadataScopeList([])
        );
        $same = new IdentityProviderSamlConfiguration(
            $this->getDefaultEntitySamlConfiguration(),
            new SingleSignOnServices([]),
            new ShibbolethMetadataScopeList([])
        );
        $differentConfiguration = new IdentityProviderSamlConfiguration(
            new EntitySamlConfiguration(
                NameIdFormat::entity(),
                new NameIdFormatList([]),
                new CertificateList([]),
                new Endpoint(Binding::httpPost(), 'some:other:uri'),
                new Endpoint(Binding::httpRedirect(), 'some:uri'),
                new ContactPersonList([])
            ),
            new SingleSignOnServices([]),
            new ShibbolethMetadataScopeList([])
        );
        $differentSingleSignOnServices = new IdentityProviderSamlConfiguration(
            $this->getDefaultEntitySamlConfiguration(),
            new SingleSignOnServices([new Endpoint(Binding::httpPost(), 'some:uri')]),
            new ShibbolethMetadataScopeList([])
        );
        $differentShibbolethMetadataScopeList = new IdentityProviderSamlConfiguration(
            $this->getDefaultEntitySamlConfiguration(),
            new SingleSignOnServices([]),
            new ShibbolethMetadataScopeList([new ShibbolethMetadataScope('OpenConext.org')])
        );

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($differentConfiguration));
        $this->assertFalse($base->equals($differentSingleSignOnServices));
        $this->assertFalse($base->equals($differentShibbolethMetadataScopeList));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_a_serialized_identity_provider_saml_configuration_yields_an_equal_value_object()
    {
        $original = new IdentityProviderSamlConfiguration(
            $this->getDefaultEntitySamlConfiguration(),
            new SingleSignOnServices([]),
            new ShibbolethMetadataScopeList()
        );

        $deserialized = IdentityProviderSamlConfiguration::deserialize($original->serialize());

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

        IdentityProviderSamlConfiguration::deserialize($notArray);
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

        IdentityProviderSamlConfiguration::deserialize($invalidData);
    }

    public function invalidDataProvider()
    {
        return [
            'no matching keys' => [
                [
                    'foo' => $this->getDefaultEntitySamlConfiguration()->serialize(),
                    'bar' => (new SingleSignOnServices([]))->serialize(),
                    'baz' => (new ShibbolethMetadataScopeList([]))->serialize()
                ]
            ],
            'no entity_saml_configuration' => [
                [
                    'single_sign_on_services'        => (new SingleSignOnServices([]))->serialize(),
                    'shibboleth_metadata_scope_list' => (new ShibbolethMetadataScopeList([]))->serialize()
                ]
            ],
            'no single_sign_on_services' => [
                [
                    'entity_saml_configuration'      => $this->getDefaultEntitySamlConfiguration()->serialize(),
                    'shibboleth_metadata_scope_list' => (new ShibbolethMetadataScopeList([]))->serialize()
                ]
            ],
            'no shibboleth_metadata_scope_list' => [
                [
                    'entity_saml_configuration' => $this->getDefaultEntitySamlConfiguration()->serialize(),
                    'single_sign_on_services'   => (new SingleSignOnServices([]))->serialize(),
                ]
            ],
        ];
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function an_identity_provider_saml_configuration_can_be_cast_to_string()
    {
        $identityProvider = new IdentityProviderSamlConfiguration(
            $this->getDefaultEntitySamlConfiguration(),
            new SingleSignOnServices([]),
            new ShibbolethMetadataScopeList([])
        );

        $this->assertInternalType('string', (string) $identityProvider);
    }

    /**
     * Helper method for instantiating an EntitiySamlConfiguration
     *
     * @return EntitySamlConfiguration
     */
    private function getDefaultEntitySamlConfiguration()
    {
        return new EntitySamlConfiguration(
            NameIdFormat::entity(),
            new NameIdFormatList([NameIdFormat::entity(), NameIdFormat::persistent()]),
            new CertificateList([new Certificate('foo')]),
            new Endpoint(Binding::httpRedirect(), 'some:uri'),
            new Endpoint(Binding::httpPost(), 'some:other:uri'),
            new ContactPersonList([
                new ContactPerson(
                    ContactType::administrative(),
                    new EmailAddressList([new EmailAddress('john.doe@domain.invalid')]),
                    new TelephoneNumberList([new TelephoneNumber('00-0-00000000')]),
                    new GivenName('John'),
                    new Surname('Doe'),
                    new Company('OpenConext')
                )
            ]),
            new Organization(
                new OrganizationNameList([new OrganizationName('OpenConext', 'nl')]),
                new OrganizationDisplayNameList([new OrganizationDisplayName('OpenConext', 'nl')]),
                new OrganizationUrlList([new OrganizationUrl('https://www.openconext.org', 'nl')])
            )
        );
    }
}
