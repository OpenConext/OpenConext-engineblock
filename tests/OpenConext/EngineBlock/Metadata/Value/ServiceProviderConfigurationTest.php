<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as UnitTest;

class ServiceProviderConfigurationTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notBoolean
     *
     * @param mixed $notBoolean
     */
    public function display_unconnected_idps_in_wayf_must_be_a_boolean($notBoolean)
    {
        $this->expectException(InvalidArgumentException::class);

        new ServiceProviderConfiguration($notBoolean, true, true, true, true, true, true);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notBoolean
     *
     * @param mixed $notBoolean
     */
    public function is_trusted_proxy_must_be_a_boolean($notBoolean)
    {
        $this->expectException(InvalidArgumentException::class);

        new ServiceProviderConfiguration(true, $notBoolean, true, true, true, true, true);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notBoolean
     *
     * @param mixed $notBoolean
     */
    public function is_transparent_issuer_must_be_a_boolean($notBoolean)
    {
        $this->expectException(InvalidArgumentException::class);

        new ServiceProviderConfiguration(true, true, $notBoolean, true, true, true, true);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notBoolean
     *
     * @param mixed $notBoolean
     */
    public function requires_consent_must_be_a_boolean($notBoolean)
    {
        $this->expectException(InvalidArgumentException::class);

        new ServiceProviderConfiguration(true, true, true, $notBoolean, true, true, true);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notBoolean
     *
     * @param mixed $notBoolean
     */
    public function denormalization_should_be_skipped_must_be_a_boolean($notBoolean)
    {
        $this->expectException(InvalidArgumentException::class);

        new ServiceProviderConfiguration(true, true, true, true, $notBoolean, true, true);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notBoolean
     *
     * @param mixed $notBoolean
     */
    public function requires_policy_enforcement_decision_must_be_a_boolean($notBoolean)
    {
        $this->expectException(InvalidArgumentException::class);

        new ServiceProviderConfiguration(true, true, true, true, true, $notBoolean, true);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notBoolean
     *
     * @param mixed $notBoolean
     */
    public function requires_attribute_aggregation_must_be_a_boolean($notBoolean)
    {
        $this->expectException(InvalidArgumentException::class);

        new ServiceProviderConfiguration(true, true, true, true, true, true, $notBoolean);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function display_unconnected_idps_in_wayf_can_be_queried()
    {
        $displayUnconnectedIdps = new ServiceProviderConfiguration(true, true, true, true, true, true, true);
        $hideUnconnectedIdps = new ServiceProviderConfiguration(false, true, true, true, true, true, true);

        $this->assertTrue($displayUnconnectedIdps->displayUnconnectedIdpsInWayf());
        $this->assertFalse($hideUnconnectedIdps->displayUnconnectedIdpsInWayf());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function trusted_proxy_can_be_queried()
    {
        $trustedProxy = new ServiceProviderConfiguration(true, true, true, true, true, true, true);
        $notTrustedProxy = new ServiceProviderConfiguration(true, false, true, true, true, true, true);

        $this->assertTrue($trustedProxy->isTrustedProxy());
        $this->assertFalse($notTrustedProxy->isTrustedProxy());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function transparent_issuer_can_be_queried()
    {
        $transparent = new ServiceProviderConfiguration(true, true, true, true, true, true, true);
        $opaque = new ServiceProviderConfiguration(true, true, false, true, true, true, true);

        $this->assertTrue($transparent->isTransparentIssuer());
        $this->assertFalse($opaque->isTransparentIssuer());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function requires_consent_can_be_queried()
    {
        $consentRequired = new ServiceProviderConfiguration(true, true, true, true, true, true, true);
        $consentNotRequired = new ServiceProviderConfiguration(true, true, true, false, true, true, true);

        $this->assertTrue($consentRequired->requiresConsent());
        $this->assertFalse($consentNotRequired->requiresConsent());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function denormalization_should_be_skipped_can_be_queried()
    {
        $denormalizationSkipped = new ServiceProviderConfiguration(true, true, true, true, true, true, true);
        $denormalizationRequired = new ServiceProviderConfiguration(true, true, true, true, false, true, true);

        $this->assertTrue($denormalizationSkipped->denormalizationShouldBeSkipped());
        $this->assertFalse($denormalizationRequired->denormalizationShouldBeSkipped());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function requires_policy_enforcement_decision_can_be_queried()
    {
        $decisionRequired = new ServiceProviderConfiguration(true, true, true, true, true, true, true);
        $noDecisionRequired = new ServiceProviderConfiguration(true, true, true, true, true, false, true);

        $this->assertTrue($decisionRequired->requiresPolicyEnforcementDecision());
        $this->assertFalse($noDecisionRequired->requiresPolicyEnforcementDecision());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function requires_attribute_aggregation_can_be_queried()
    {
        $aggregation = new ServiceProviderConfiguration(true, true, true, true, true, true, true);
        $noAggregation = new ServiceProviderConfiguration(true, true, true, true, true, true, false);

        $this->assertTrue($aggregation->requiresAttributeAggregation());
        $this->assertFalse($noAggregation->requiresAttributeAggregation());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function equality_is_verified_on_all_properies()
    {
        $base                    = new ServiceProviderConfiguration(true, true, true, true, true, true, true);
        $same                    = new ServiceProviderConfiguration(true, true, true, true, true, true, true);
        $hideIdpsInWayf          = new ServiceProviderConfiguration(false, true, true, true, true, true, true);
        $notTrustedProxy         = new ServiceProviderConfiguration(true, false, true, true, true, true, true);
        $notTransparent          = new ServiceProviderConfiguration(true, true, false, true, true, true, true);
        $consentNotRequired      = new ServiceProviderConfiguration(true, true, true, false, true, true, true);
        $denormalizationRequired = new ServiceProviderConfiguration(true, true, true, true, false, true, true);
        $noDecisionRequired      = new ServiceProviderConfiguration(true, true, true, true, true, false, true);
        $noAggregation           = new ServiceProviderConfiguration(true, true, true, true, true, true, false);

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($hideIdpsInWayf));
        $this->assertFalse($base->equals($notTrustedProxy));
        $this->assertFalse($base->equals($notTransparent));
        $this->assertFalse($base->equals($consentNotRequired));
        $this->assertFalse($base->equals($denormalizationRequired));
        $this->assertFalse($base->equals($noDecisionRequired));
        $this->assertFalse($base->equals($noAggregation));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_a_serializated_service_provider_configuration_yiels_an_equal_value_object()
    {
        $original = new ServiceProviderConfiguration(true, true, true, true, true, true, true);

        $deserialized = ServiceProviderConfiguration::deserialize($original->serialize());

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

        ServiceProviderConfiguration::deserialize($notArray);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider invalidDataKeysProvider
     *
     * @param array $data
     */
    public function deserialization_requires_the_presence_of_all_required_keys($data)
    {
        $this->expectException(InvalidArgumentException::class);

        ServiceProviderConfiguration::deserialize($data);
    }

    /**
     * @return array
     */
    public function invalidDataKeysProvider()
    {
        return [
            'no valid keys' => [['foo', 'bar']],
            'no display_unconnect_idps_in_wayf' => [[
                'is_trusted_proxy'                    => true,
                'is_transparent_issuer'                => true,
                'requires_consent'                     => true,
                'denormalization_should_be_skipped'    => true,
                'requires_policy_enforcement_decision' => true,
                'requires_attribute_aggregation'       => true
            ]],
            'no is_trusted_proxy'                     => [[
                'display_unconnect_idps_in_wayf'       => true,
                'is_transparent_issuer'                => true,
                'requires_consent'                     => true,
                'denormalization_should_be_skipped'    => true,
                'requires_policy_enforcement_decision' => true,
                'requires_attribute_aggregation'       => true
            ]],
            'no is_transparent_issuer'                => [[
                'display_unconnect_idps_in_wayf'       => true,
                'is_trusted_proxy'                     => true,
                'requires_consent'                     => true,
                'denormalization_should_be_skipped'    => true,
                'requires_policy_enforcement_decision' => true,
                'requires_attribute_aggregation'       => true
            ]],
            'no requires_consent'                     => [[
                'display_unconnect_idps_in_wayf'       => true,
                'is_trusted_proxy'                     => true,
                'is_transparent_issuer'                => true,
                'denormalization_should_be_skipped'    => true,
                'requires_policy_enforcement_decision' => true,
                'requires_attribute_aggregation'       => true
            ]],
            'no denormalization_should_be_skipped'    => [[
                'display_unconnect_idps_in_wayf'       => true,
                'is_trusted_proxy'                     => true,
                'is_transparent_issuer'                => true,
                'requires_consent'                     => true,
                'requires_policy_enforcement_decision' => true,
                'requires_attribute_aggregation'       => true
            ]],
            'no requires_policy_enforcement_decision' => [[
                'display_unconnect_idps_in_wayf'       => true,
                'is_trusted_proxy'                     => true,
                'is_transparent_issuer'                => true,
                'requires_consent'                     => true,
                'denormalization_should_be_skipped'    => true,
                'requires_attribute_aggregation'       => true
            ]],
            'no requires_attribute_aggregation'       => [[
                'display_unconnect_idps_in_wayf'       => true,
                'is_trusted_proxy'                     => true,
                'is_transparent_issuer'                => true,
                'requires_consent'                     => true,
                'denormalization_should_be_skipped'    => true,
                'requires_policy_enforcement_decision' => true,
            ]],
        ];
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function a_service_provider_configuration_can_be_cast_to_string()
    {
        $configuration = new ServiceProviderConfiguration(true, true, true, true, true, true, true);

        $this->assertInternalType('string', (string) $configuration);
    }
}
