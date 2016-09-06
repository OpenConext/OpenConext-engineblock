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
use OpenConext\Value\Saml\NameIdFormat;
use OpenConext\Value\Saml\NameIdFormatList;
use PHPUnit_Framework_TestCase as UnitTest;

class EntitySamlConfigurationTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function preferred_name_id_format_can_be_retrieved()
    {
        $preferredNameIdFormat = NameIdFormat::entity();

        $entitySamlConfiguration = new EntitySamlConfiguration(
            $preferredNameIdFormat,
            new NameIdFormatList([]),
            new CertificateList([]),
            new Endpoint(Binding::httpPost(), 'single:logout:uri'),
            new Endpoint(Binding::httpPost(), 'response:processing:service:uri'),
            new ContactPersonList([]),
            $this->getOpenConextOrganization()
        );

        $this->assertEquals($preferredNameIdFormat, $entitySamlConfiguration->getPreferredNameIdFormat());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function allowed_name_id_formats_can_be_retrieved()
    {
        $allowedNameIdFormats = new NameIdFormatList([NameIdFormat::entity()]);

        $entitySamlConfiguration = new EntitySamlConfiguration(
            NameIdFormat::entity(),
            $allowedNameIdFormats,
            new CertificateList([]),
            new Endpoint(Binding::httpPost(), 'single:logout:uri'),
            new Endpoint(Binding::httpPost(), 'response:processing:service:uri'),
            new ContactPersonList([]),
            $this->getOpenConextOrganization()
        );

        $this->assertEquals($allowedNameIdFormats, $entitySamlConfiguration->getAllowedNameIdFormats());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function certificate_list_can_be_retrieved()
    {
        $certificateList = new CertificateList([]);

        $entitySamlConfiguration = new EntitySamlConfiguration(
            NameIdFormat::entity(),
            new NameIdFormatList([]),
            $certificateList,
            new Endpoint(Binding::httpPost(), 'single:logout:uri'),
            new Endpoint(Binding::httpPost(), 'response:processing:service:uri'),
            new ContactPersonList([]),
            $this->getOpenConextOrganization()
        );

        $this->assertEquals($certificateList, $entitySamlConfiguration->getCertificateList());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function single_logout_service_can_be_retrieved()
    {
        $singleLogoutService = new Endpoint(Binding::httpPost(), 'single:logout:uri');

        $entitySamlConfiguration = new EntitySamlConfiguration(
            NameIdFormat::entity(),
            new NameIdFormatList([]),
            new CertificateList([]),
            $singleLogoutService,
            new Endpoint(Binding::httpPost(), 'response:processing:service:uri'),
            new ContactPersonList([]),
            $this->getOpenConextOrganization()
        );

        $this->assertEquals($singleLogoutService, $entitySamlConfiguration->getSingleLogoutService());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function response_processing_service_can_be_retrieved()
    {
        $responseProcessingService = new Endpoint(Binding::httpPost(), 'response:processing:service:uri');

        $entitySamlConfiguration = new EntitySamlConfiguration(
            NameIdFormat::entity(),
            new NameIdFormatList([]),
            new CertificateList([]),
            new Endpoint(Binding::httpPost(), 'single:logout:uri'),
            $responseProcessingService,
            new ContactPersonList([]),
            $this->getOpenConextOrganization()
        );

        $this->assertEquals($responseProcessingService, $entitySamlConfiguration->getResponseProcessingService());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function contact_persons_can_be_retrieved()
    {
        $contactPersonList = new ContactPersonList([]);

        $entitySamlConfiguration = new EntitySamlConfiguration(
            NameIdFormat::entity(),
            new NameIdFormatList([]),
            new CertificateList([]),
            new Endpoint(Binding::httpPost(), 'single:logout:uri'),
            new Endpoint(Binding::httpPost(), 'response:processing:service:uri'),
            $contactPersonList,
            $this->getOpenConextOrganization()
        );

        $this->assertEquals($contactPersonList, $entitySamlConfiguration->getContactPersons());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function organization_can_be_retrieved()
    {
        $organization = $this->getOpenConextOrganization();

        $entitySamlConfiguration = new EntitySamlConfiguration(
            NameIdFormat::entity(),
            new NameIdFormatList([]),
            new CertificateList([]),
            new Endpoint(Binding::httpPost(), 'single:logout:uri'),
            new Endpoint(Binding::httpPost(), 'response:processing:service:uri'),
            new ContactPersonList([]),
            $organization
        );

        $this->assertEquals($organization, $entitySamlConfiguration->getOrganization());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function equality_is_verified_on_all_properties()
    {
        $base = new EntitySamlConfiguration(
            NameIdFormat::entity(),
            new NameIdFormatList([]),
            new CertificateList([]),
            new Endpoint(Binding::httpPost(), 'single:logout:uri'),
            new Endpoint(Binding::httpPost(), 'response:processing:service:uri'),
            new ContactPersonList([]),
            $this->getOpenConextOrganization()
        );
        $same = new EntitySamlConfiguration(
            NameIdFormat::entity(),
            new NameIdFormatList([]),
            new CertificateList([]),
            new Endpoint(Binding::httpPost(), 'single:logout:uri'),
            new Endpoint(Binding::httpPost(), 'response:processing:service:uri'),
            new ContactPersonList([]),
            $this->getOpenConextOrganization()
        );
        $differentNameIdFormat = new EntitySamlConfiguration(
            NameIdFormat::emailAddress(),
            new NameIdFormatList([]),
            new CertificateList([]),
            new Endpoint(Binding::httpPost(), 'single:logout:uri'),
            new Endpoint(Binding::httpPost(), 'response:processing:service:uri'),
            new ContactPersonList([]),
            $this->getOpenConextOrganization()
        );
        $differentAllowedNameIdFormats = new EntitySamlConfiguration(
            NameIdFormat::entity(),
            new NameIdFormatList([NameIdFormat::entity()]),
            new CertificateList([]),
            new Endpoint(Binding::httpPost(), 'single:logout:uri'),
            new Endpoint(Binding::httpPost(), 'response:processing:service:uri'),
            new ContactPersonList([]),
            $this->getOpenConextOrganization()
        );
        $differentCertificateList = new EntitySamlConfiguration(
            NameIdFormat::entity(),
            new NameIdFormatList([]),
            new CertificateList([new Certificate('foo')]),
            new Endpoint(Binding::httpPost(), 'single:logout:uri'),
            new Endpoint(Binding::httpPost(), 'response:processing:service:uri'),
            new ContactPersonList([]),
            $this->getOpenConextOrganization()
        );
        $differentSingleLogoutService = new EntitySamlConfiguration(
            NameIdFormat::entity(),
            new NameIdFormatList([]),
            new CertificateList([]),
            new Endpoint(Binding::httpRedirect(), 'other:logout:uri'),
            new Endpoint(Binding::httpPost(), 'response:processing:service:uri'),
            new ContactPersonList([]),
            $this->getOpenConextOrganization()
        );
        $differentResponseProcessingService = new EntitySamlConfiguration(
            NameIdFormat::entity(),
            new NameIdFormatList([]),
            new CertificateList([]),
            new Endpoint(Binding::httpPost(), 'single:logout:uri'),
            new Endpoint(Binding::httpPost(), 'different:processing:service:uri'),
            new ContactPersonList([]),
            $this->getOpenConextOrganization()
        );
        $differentContactPersons = new EntitySamlConfiguration(
            NameIdFormat::entity(),
            new NameIdFormatList([]),
            new CertificateList([]),
            new Endpoint(Binding::httpPost(), 'single:logout:uri'),
            new Endpoint(Binding::httpPost(), 'response:processing:service:uri'),
            new ContactPersonList([$this->getJohnDoeContactPerson()]),
            $this->getOpenConextOrganization()
        );
        $differentOrganization = new EntitySamlConfiguration(
            NameIdFormat::entity(),
            new NameIdFormatList([]),
            new CertificateList([]),
            new Endpoint(Binding::httpPost(), 'single:logout:uri'),
            new Endpoint(Binding::httpPost(), 'response:processing:service:uri'),
            new ContactPersonList([$this->getJohnDoeContactPerson()]),
            $this->getFoobarOrganization()
        );
        $nullOrganization = new EntitySamlConfiguration(
            NameIdFormat::entity(),
            new NameIdFormatList([]),
            new CertificateList([]),
            new Endpoint(Binding::httpPost(), 'single:logout:uri'),
            new Endpoint(Binding::httpPost(), 'response:processing:service:uri'),
            new ContactPersonList([$this->getJohnDoeContactPerson()])
        );

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($differentNameIdFormat));
        $this->assertFalse($base->equals($differentAllowedNameIdFormats));
        $this->assertFalse($base->equals($differentCertificateList));
        $this->assertFalse($base->equals($differentSingleLogoutService));
        $this->assertFalse($base->equals($differentResponseProcessingService));
        $this->assertFalse($base->equals($differentContactPersons));
        $this->assertFalse($base->equals($differentOrganization));
        $this->assertFalse($base->equals($nullOrganization));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_a_serialized_entity_saml_configuration_yields_an_equal_value_object()
    {
        $original = new EntitySamlConfiguration(
            NameIdFormat::entity(),
            new NameIdFormatList([]),
            new CertificateList([]),
            new Endpoint(Binding::httpPost(), 'single:logout:uri'),
            new Endpoint(Binding::httpPost(), 'response:processing:service:uri'),
            new ContactPersonList([]),
            $this->getOpenConextOrganization()
        );

        $deserialized = EntitySamlConfiguration::deserialize($original->serialize());

        $this->assertTrue($original->equals($deserialized));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notArray
     *
     * @param mixed $notArray
     */
    public function deserialization_requires_data_to_be_an_array($notArray)
    {
        $this->expectException(InvalidArgumentException::class);

        EntitySamlConfiguration::deserialize($notArray);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider invalidDataProvider()
     *
     * @param array $invalidData
     */
    public function deserialization_enforces_the_presence_of_all_required_keys($invalidData)
    {
        $this->expectException(InvalidArgumentException::class);

        EntitySamlConfiguration::deserialize($invalidData);
    }

    public function invalidDataProvider()
    {
        return [
            'no preferred_name_id_format' => [
                [
                    'allowed_name_id_formats'     => [],
                    'certificate_list'            => [],
                    'single_logout_service'       => 'some:logout:uri',
                    'response_processing_service' => 'response:processing:uri',
                    'contact_person_list'         => [],
                    'organization'                => null
                ]
            ],
            'no allowed_name_id_formats' => [
                [
                    'preferred_name_id_format'    => NameIdFormat::EMAIL_ADDRESS,
                    'certificate_list'            => [],
                    'single_logout_service'       => 'some:logout:uri',
                    'response_processing_service' => 'response:processing:uri',
                    'contact_person_list'         => [],
                    'organization'                => null
                ]
            ],
            'no certificate_list' => [
                [
                    'preferred_name_id_format'    => NameIdFormat::EMAIL_ADDRESS,
                    'allowed_name_id_formats'     => [],
                    'single_logout_service'       => 'some:logout:uri',
                    'response_processing_service' => 'response:processing:uri',
                    'contact_person_list'         => [],
                    'organization'                => null
                ]
            ],
            'no single_logout_service' => [
                [
                    'preferred_name_id_format'    => NameIdFormat::EMAIL_ADDRESS,
                    'allowed_name_id_formats'     => [],
                    'certificate_list'            => [],
                    'response_processing_service' => 'response:processing:uri',
                    'contact_person_list'         => [],
                    'organization'                => null
                ]
            ],
            'no response_processing_service' => [
                [
                    'preferred_name_id_format'    => NameIdFormat::EMAIL_ADDRESS,
                    'allowed_name_id_formats'     => [],
                    'certificate_list'            => [],
                    'single_logout_service'       => 'some:logout:uri',
                    'contact_person_list'         => [],
                    'organization'                => null
                ]
            ],
            'no contact_person_list' => [
                [
                    'preferred_name_id_format'    => NameIdFormat::EMAIL_ADDRESS,
                    'allowed_name_id_formats'     => [],
                    'certificate_list'            => [],
                    'single_logout_service'       => 'some:logout:uri',
                    'response_processing_service' => 'response:processing:uri',
                    'organization'                => null
                ]
            ],
            'no organization' => [
                [
                    'preferred_name_id_format' => NameIdFormat::EMAIL_ADDRESS,
                    'allowed_name_id_formats' => [],
                    'certificate_list' => [],
                    'single_logout_service' => 'some:logout:uri',
                    'response_processing_service' => 'response:processing:uri',
                    'contact_person_list' => [],
                ]
            ]
        ];
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function an_entity_saml_configuration_can_be_cast_to_string()
    {
        $entitySamlConfiguration = new EntitySamlConfiguration(
            NameIdFormat::entity(),
            new NameIdFormatList([]),
            new CertificateList([]),
            new Endpoint(Binding::httpPost(), 'single:logout:uri'),
            new Endpoint(Binding::httpPost(), 'response:processing:service:uri'),
            new ContactPersonList([]),
            $this->getOpenConextOrganization()
        );

        $this->assertInternalType('string', (string) $entitySamlConfiguration);
    }

    /**
     * @return Organization
     */
    public function getOpenConextOrganization()
    {
        return new Organization(
            new OrganizationNameList([new OrganizationName('OpenConext.org', 'en')]),
            new OrganizationDisplayNameList([new OrganizationDisplayName('OpenConext', 'en')]),
            new OrganizationUrlList([new OrganizationUrl('https://www.openconext.org', 'en')])
        );
    }

    /**
     * @return Organization
     */
    public function getFoobarOrganization()
    {
        return new Organization(
            new OrganizationNameList([new OrganizationName('Foobar.org', 'en')]),
            new OrganizationDisplayNameList([new OrganizationDisplayName('FooBar', 'en')]),
            new OrganizationUrlList([new OrganizationUrl('https://foobar', 'en')])
        );
    }

    /**
     * @return ContactPerson
     */
    private function getJohnDoeContactPerson()
    {
        return new ContactPerson(
            ContactType::technical(),
            new EmailAddressList([new EmailAddress('john.doe@domain.invalid')]),
            new TelephoneNumberList([new TelephoneNumber('123987')]),
            new GivenName('John'),
            new Surname('Doe'),
            new Company('OpenConext.org')
        );
    }
}
