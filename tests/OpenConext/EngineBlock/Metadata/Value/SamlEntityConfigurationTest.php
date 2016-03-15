<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as UnitTest;

class SamlEntityConfigurationTest extends UnitTest
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
    public function requires_additional_logging_must_be_a_boolean($notBoolean)
    {
        $this->expectException(InvalidArgumentException::class);

        new SamlEntityConfiguration($notBoolean, true, true);
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
    public function disabled_scoping_must_be_a_boolean($notBoolean)
    {
        $this->expectException(InvalidArgumentException::class);

        new SamlEntityConfiguration(true, $notBoolean, true);
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
    public function requires_signed_requests_must_be_a_boolean($notBoolean)
    {
        $this->expectException(InvalidArgumentException::class);

        new SamlEntityConfiguration(true, true, $notBoolean);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function additional_logging_requirement_can_be_queried()
    {
        $requiresAdditionalLogging      = new SamlEntityConfiguration(true, true, true);
        $doesNotRequireAdditionaLogging = new SamlEntityConfiguration(false, true, true);

        $this->assertTrue($requiresAdditionalLogging->requiresAdditionalLogging());
        $this->assertFalse($doesNotRequireAdditionaLogging->requiresAdditionalLogging());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function disabled_scoping_can_be_queried()
    {
        $disablesScoping = new SamlEntityConfiguration(true, true, true);
        $enablesScoping  = new SamlEntityConfiguration(true, false, true);

        $this->assertTrue($disablesScoping->isScopingDisabled());
        $this->assertFalse($enablesScoping->isScopingDisabled());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function signed_request_requirement_can_be_queried()
    {
        $requiresSignedRequests       = new SamlEntityConfiguration(true, true, true);
        $doesNotRequireSignedRequests = new SamlEntityConfiguration(true, true, false);

        $this->assertTrue($requiresSignedRequests->requiresSignedRequests());
        $this->assertFalse($doesNotRequireSignedRequests->requiresSignedRequests());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function equality_is_verified_on_all_configuration_values()
    {
        $base                = new SamlEntityConfiguration(true, true, true);
        $same                = new SamlEntityConfiguration(true, true, true);
        $noAdditionalLogging = new SamlEntityConfiguration(false, true, true);
        $enabledScoping      = new SamlEntityConfiguration(true, false, true);
        $noSignedRequests    = new SamlEntityConfiguration(true, true, false);

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($noAdditionalLogging));
        $this->assertFalse($base->equals($enabledScoping));
        $this->assertFalse($base->equals($noSignedRequests));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_serialized_saml_entity_configuration_yields_an_equal_value_object()
    {
        $original = new SamlEntityConfiguration(true, true, true);

        $deserialized = SamlEntityConfiguration::deserialize($original->serialize());

        $this->assertTrue($original->equals($deserialized));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function it_can_be_cast_to_string()
    {
        $original = new SamlEntityConfiguration(true, true, true);

        $this->assertInternalType('string', (string) $original);
    }
}
