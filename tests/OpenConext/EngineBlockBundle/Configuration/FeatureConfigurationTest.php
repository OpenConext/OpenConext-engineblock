<?php

namespace OpenConext\EngineBlockBundle\Configuration;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlock\Exception\LogicException;
use PHPUnit_Framework_TestCase as UnitTest;
use stdClass;

class FeatureConfigurationTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function all_features_must_be_an_instance_of_feature()
    {
        $features = [
            'some.feature' => new Feature('some.feature', true),
            'other.feature' => new Feature('other.feature', false),
            'foo' => new stdClass()
        ];

        $this->expectException(InvalidArgumentException::class);

        new FeatureConfiguration($features);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function all_features_must_have_a_string_key()
    {
        $features = [
            'some.feature'  => new Feature('some.feature', true),
            1 => new Feature('other.feature', false),
        ];

        $this->expectException(InvalidArgumentException::class);

        new FeatureConfiguration($features);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function a_feature_can_be_queried_for_presence()
    {
        $features = [
            'some.feature'  => new Feature('some.feature', true),
            'other.feature' => new Feature('other.feature', false)
        ];

        $featureConfiguration = new FeatureConfiguration($features);

        $this->assertTrue($featureConfiguration->hasFeature('some.feature'));
        $this->assertTrue($featureConfiguration->hasFeature('other.feature'));
        $this->assertFalse($featureConfiguration->hasFeature('not.configured'));
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function a_feature_is_correctly_reported_to_be_enabled()
    {
        $features = [
            'some.feature'  => new Feature('some.feature', true),
            'disabled.feature' => new Feature('disabled.feature', false)
        ];

        $featureConfiguration = new FeatureConfiguration($features);

        $this->assertTrue($featureConfiguration->isEnabled('some.feature'));
        $this->assertFalse($featureConfiguration->isEnabled('disabled.feature'));
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function querying_whether_a_not_configured_feature_is_enabled_causes_an_exception_to_be_thrown()
    {
        $features = [
            'some.feature'  => new Feature('some.feature', true),
            'other.feature' => new Feature('other.feature', false)
        ];

        $featureConfiguration = new FeatureConfiguration($features);
        
        $this->expectException(LogicException::class);
        
        $featureConfiguration->isEnabled('not.configured');
    }
}
