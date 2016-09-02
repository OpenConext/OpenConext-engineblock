<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntitySet;
use OpenConext\Value\Saml\EntityType;
use PHPUnit_Framework_TestCase as UnitTest;

class IdentityProviderConfigurationTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function saml_entity_configuration_can_be_retrieved()
    {
        $samlEntityConfiguration = $this->getDefaultSamlEntityConfiguration();

        $identityProviderConfiguration = new IdentityProviderConfiguration(
            $samlEntityConfiguration,
            new EntitySet([]),
            GuestQualifier::all()
        );

        $this->assertSame($samlEntityConfiguration, $identityProviderConfiguration->getEntityConfiguration());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function service_providers_without_consent_can_be_retrieved()
    {
        $spWithoutConsent = new EntitySet([new Entity(new EntityId('OpenConext'), EntityType::IdP())]);

        $identityProviderConfiguration = new IdentityProviderConfiguration(
            $this->getDefaultSamlEntityConfiguration(),
            $spWithoutConsent,
            GuestQualifier::all()
        );

        $this->assertSame($spWithoutConsent, $identityProviderConfiguration->getServiceProvidersWithoutConsent());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function guest_qualifier_can_be_retrieved()
    {
        $guestQualifier = GuestQualifier::none();

        $identityProviderConfiguration = new IdentityProviderConfiguration(
            $this->getDefaultSamlEntityConfiguration(),
            new EntitySet(),
            $guestQualifier
        );

        $this->assertSame($guestQualifier, $identityProviderConfiguration->getGuestQualifier());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function equality_is_verified_on_all_properties()
    {
        $base = new IdentityProviderConfiguration(
            $this->getDefaultSamlEntityConfiguration(),
            new EntitySet([]),
            GuestQualifier::none()
        );
        $same = new IdentityProviderConfiguration(
            $this->getDefaultSamlEntityConfiguration(),
            new EntitySet([]),
            GuestQualifier::none()
        );
        $differentSamlEntityConfiguration = new IdentityProviderConfiguration(
            $this->getDifferentSamlEntityConfiguration(),
            new EntitySet([]),
            GuestQualifier::none()
        );
        $differentServiceProvidersWithoutConsent = new IdentityProviderConfiguration(
            $this->getDefaultSamlEntityConfiguration(),
            new EntitySet([new Entity(new EntityId('OpenConext'), EntityType::IdP())]),
            GuestQualifier::none()
        );
        $differentGuestQualifier = new IdentityProviderConfiguration(
            $this->getDefaultSamlEntityConfiguration(),
            new EntitySet([]),
            GuestQualifier::all()
        );

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($differentSamlEntityConfiguration));
        $this->assertFalse($base->equals($differentServiceProvidersWithoutConsent));
        $this->assertFalse($base->equals($differentGuestQualifier));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_a_serialized_identity_provider_configuration_yields_an_equal_value_object()
    {
        $original = new IdentityProviderConfiguration(
            $this->getDefaultSamlEntityConfiguration(),
            new EntitySet([]),
            GuestQualifier::none()
        );

        $deserialized = IdentityProviderConfiguration::deserialize($original->serialize());

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

        IdentityProviderConfiguration::deserialize($notArray);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider invalidDataProvider
     *
     * @param $invalidData
     */
    public function deserialization_enforces_presence_of_all_required_keys($invalidData)
    {
        $this->expectException(InvalidArgumentException::class);

        IdentityProviderConfiguration::deserialize($invalidData);
    }

    public function invalidDataProvider()
    {
        $serializedEntityConfiguration = $this->getDefaultSamlEntityConfiguration()->serialize();

        return [
            'no matches' => [
                [
                    'foo' => $serializedEntityConfiguration,
                    'bar' => [],
                    'baz' => GuestQualifier::QUALIFIER_ALL
                ]
            ],
            'no saml_entity_configuration' => [
                [
                    'service_providers_without_consent' => [],
                    'guest_qualifier' => GuestQualifier::QUALIFIER_ALL
                ]
            ],
            'no service_providers_without_consent' => [
                [
                    'saml_entity_configuration' => $serializedEntityConfiguration,
                    'guest_qualifier' => GuestQualifier::QUALIFIER_ALL
                ]
            ],
            'no guest_qualifier' => [
                [
                    'saml_entity_configuration' => $serializedEntityConfiguration,
                    'service_providers_without_consent' => [],
                ]
            ]
        ];
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function an_identity_provider_configuration_can_be_cast_to_string()
    {
        $identityProviderConfiguration = new IdentityProviderConfiguration(
            $this->getDefaultSamlEntityConfiguration(),
            new EntitySet([]),
            GuestQualifier::all()
        );

        $this->assertInternalType('string', (string) $identityProviderConfiguration);
    }

    private function getDefaultSamlEntityConfiguration()
    {
        return new EntityConfiguration(
            new AttributeManipulationCode('echo $foo;'),
            WorkflowState::prodaccepted(),
            true,
            true,
            true
        );
    }

    private function getDifferentSamlEntityConfiguration()
    {
        return new EntityConfiguration(
            new AttributeManipulationCode('echo $bar;'),
            WorkflowState::testaccepted(),
            false,
            false,
            false
        );
    }
}
