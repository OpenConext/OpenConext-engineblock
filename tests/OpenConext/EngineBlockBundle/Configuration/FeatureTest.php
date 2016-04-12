<?php

namespace OpenConext\EngineBlockBundle\Configuration;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as UnitTest;

class FeatureTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     * @param mixed $notStringOrEmtpyString
     */
    public function feature_key_is_required_to_be_a_non_empty_string($notStringOrEmtpyString)
    {
        $this->expectException(InvalidArgumentException::class);

        new Feature($notStringOrEmtpyString, true);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     * @dataProvider \OpenConext\TestDataProvider::notBoolean
     * @param mixed $notBoolean
     */
    public function is_enabled_must_be_a_boolean($notBoolean)
    {
        $this->expectException(InvalidArgumentException::class);

        new Feature('some.feature', $notBoolean);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function an_feature_created_as_enabled_is_enabled()
    {
        $feature = new Feature('some.feature', true);

        $this->assertTrue($feature->isEnabled());
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function an_feature_created_as_disabled_is_disabled()
    {
        $feature = new Feature('some.feature', false);

        $this->assertFalse($feature->isEnabled());
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function the_feature_key_can_be_retrieved()
    {
        $featureKey = 'some.feature.key';
        $feature = new Feature($featureKey, true);

        $this->assertSame($featureKey, $feature->getFeatureKey());
    }
}
