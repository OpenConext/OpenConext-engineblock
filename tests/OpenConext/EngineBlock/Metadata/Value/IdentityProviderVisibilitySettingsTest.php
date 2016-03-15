<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as UnitTest;

class IdentityProviderVisibilitySettingsTest extends UnitTest
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
    public function is_hidden_must_be_a_boolean($notBoolean)
    {
        $this->expectException(InvalidArgumentException::class);

        new IdentityProviderVisibilitySettings($notBoolean, true);
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
    public function enabled_in_wayf_must_be_a_boolean($notBoolean)
    {
        $this->expectException(InvalidArgumentException::class);

        new IdentityProviderVisibilitySettings(true, $notBoolean);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function is_hidden_can_be_queried()
    {
        $hidden = new IdentityProviderVisibilitySettings(true, true);
        $notHidden = new IdentityProviderVisibilitySettings(false, true);

        $this->assertTrue($hidden->isHidden());
        $this->assertFalse($notHidden->isHidden());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function enabled_in_wayf_can_be_queried()
    {
        $enabledInWayf = new IdentityProviderVisibilitySettings(true, true);
        $disabledInWaf = new IdentityProviderVisibilitySettings(true, false);

        $this->assertTrue($enabledInWayf->isEnabledInWay());
        $this->assertFalse($disabledInWaf->isEnabledInWay());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function equality_is_verified_on_is_hidden_and_enabled_in_wayf()
    {
        $base           = new IdentityProviderVisibilitySettings(true, true);
        $same           = new IdentityProviderVisibilitySettings(true, true);
        $hidden         = new IdentityProviderVisibilitySettings(false, true);
        $disabledInWayf = new IdentityProviderVisibilitySettings(true, false);
        $bothFalse      = new IdentityProviderVisibilitySettings(false, false);

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($hidden));
        $this->assertFalse($base->equals($disabledInWayf));
        $this->assertFalse($base->equals($bothFalse));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_serialized_identity_provider_visibility_settings_yields_an_equal_value_object()
    {
        $original = new IdentityProviderVisibilitySettings(true, true);

        $deserialized = IdentityProviderVisibilitySettings::deserialize($original->serialize());

        $this->assertTrue($original->equals($deserialized));
    }

    /**
     * @test
     * @group        engineblock
     * @group        metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notArray
     *
     * @param mixed $notArray
     */
    public function deserialization_requires_data_to_be_an_array($notArray)
    {
        $this->expectException(InvalidArgumentException::class);

        IdentityProviderVisibilitySettings::deserialize($notArray);
    }

    /**
     * @test
     * @group        engineblock
     * @group        metadata
     *
     * @dataProvider invalidDeserializationDataProvider
     *
     * @param array $invalidData
     */
    public function deserialization_requires_hidden_and_enabled_in_wayf_as_keys($invalidData)
    {
        $this->expectException(InvalidArgumentException::class);

        IdentityProviderVisibilitySettings::deserialize($invalidData);
    }

    public function invalidDeserializationDataProvider()
    {
        return [
            'missing hidden'         => [['enbled_in_wayf' => true]],
            'missing enbled_in_wayf' => [['hidden' => true]],
            'missing both'           => [['some' => 'thing']]
        ];
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function it_can_be_cast_to_string()
    {
        $original = new IdentityProviderVisibilitySettings(true, true);

        $this->assertInternalType('string', (string) $original);
    }
}
