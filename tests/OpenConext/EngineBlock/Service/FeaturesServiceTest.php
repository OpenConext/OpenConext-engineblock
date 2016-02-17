<?php

namespace OpenConext\EngineBlock\Service;

use PHPUnit_Framework_TestCase as UnitTest;

class FeaturesServiceTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group FeatureToggle
     *
     * @dataProvider \OpenConext\TestDataProvider::notBoolean
     * @expectedException \OpenConext\EngineBlock\Exception\InvalidArgumentException
     *
     * @param mixed $notBoolean
     */
    public function metadata_push_enabled_is_required_to_be_a_boolean($notBoolean)
    {
        new FeaturesService($notBoolean, true, true);
    }

    /**
     * @test
     * @group EngineBlock
     * @group FeatureToggle
     *
     * @dataProvider \OpenConext\TestDataProvider::notBoolean
     * @expectedException \OpenConext\EngineBlock\Exception\InvalidArgumentException
     *
     * @param mixed $notBoolean
     */
    public function consent_listing_enabled_is_required_to_be_a_boolean($notBoolean)
    {
        new FeaturesService(true, $notBoolean, true);
    }

    /**
     * @test
     * @group EngineBlock
     * @group FeatureToggle
     *
     * @dataProvider \OpenConext\TestDataProvider::notBoolean
     * @expectedException \OpenConext\EngineBlock\Exception\InvalidArgumentException
     *
     * @param mixed $notBoolean
     */
    public function metadata_api_enabled_is_required_to_be_a_boolean($notBoolean)
    {
        new FeaturesService(true, true, $notBoolean);
    }

    /**
     * @test
     * @group        EngineBlock
     * @group        FeatureToggle
     */
    public function features_can_be_configured_independently()
    {
        $service = new FeaturesService(true, false, true);

        $this->assertTrue($service->metadataPushIsEnabled());
        $this->assertFalse($service->consentListingIsEnabled());
        $this->assertTrue($service->metadataApiIsEnabled());
    }
}
