<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlock\Metadata\Value\Common\LocalizedText;
use OpenConext\Value\Saml\Metadata\Common\LocalizedName;
use PHPUnit_Framework_TestCase as UnitTest;

class IdentityProviderAttributesTest extends UnitTest
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

        new IdentityProviderAttributes($this->getEntityAttributes(), $notBoolean, true, new Keywords([]));
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

        new IdentityProviderAttributes($this->getEntityAttributes(), true, $notBoolean, new Keywords([]));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function service_name_can_be_retrieved()
    {
        $serviceName = new LocalizedServiceName([new LocalizedName('OpenConext', 'en')]);

        $attributes = new IdentityProviderAttributes(
            new EntityAttributes($serviceName, new LocalizedDescription([]), new Logo('url', 1, 1)),
            true,
            true,
            new Keywords([])
        );

        $this->assertSame($serviceName, $attributes->getServiceName());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function description_can_be_retrieved()
    {
        $description = new LocalizedDescription([new LocalizedText('OpenConext', 'en')]);

        $attributes = new IdentityProviderAttributes(
            new EntityAttributes(new LocalizedServiceName([]), $description, new Logo('url', 1, 1)),
            true,
            true,
            new Keywords([])
        );

        $this->assertSame($description, $attributes->getDescription());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function logo_can_be_retrieved()
    {
        $logo = new Logo('https://cdn.domain.invalid/logo.png', 150, 150);

        $attributes = new IdentityProviderAttributes(
            new EntityAttributes(new LocalizedServiceName([]), new LocalizedDescription([]), $logo),
            true,
            true,
            new Keywords([])
        );

        $this->assertSame($logo, $attributes->getLogo());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function is_hidden_can_be_queried()
    {
        $hidden = new IdentityProviderAttributes($this->getEntityAttributes(), true, true, new Keywords([]));
        $notHidden = new IdentityProviderAttributes($this->getEntityAttributes(), false, true, new Keywords([]));

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
        $enabledInWayf = new IdentityProviderAttributes($this->getEntityAttributes(), true, true, new Keywords([]));
        $disabledInWaf = new IdentityProviderAttributes($this->getEntityAttributes(), true, false, new Keywords([]));

        $this->assertTrue($enabledInWayf->isEnabledInWay());
        $this->assertFalse($disabledInWaf->isEnabledInWay());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function keywords_can_be_retrieved()
    {
        $keywords = new Keywords([new LocalizedKeywords('en', ['key', 'word'])]);

        $attributes = new IdentityProviderAttributes($this->getEntityAttributes(), true, true, $keywords);

        $this->assertSame($keywords, $attributes->getKeywords());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function localized_keywords_for_a_given_locale_can_be_retrieved()
    {
        $englishKeywords = new LocalizedKeywords('en', ['key', 'word']);

        $attributes = new IdentityProviderAttributes(
            $this->getEntityAttributes(),
            true,
            true,
            new Keywords([$englishKeywords])
        );

        $this->assertNull($attributes->getKeywordsForLocale('nl'));
        $this->assertEquals($englishKeywords, $attributes->getKeywordsForLocale('en'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function equality_is_verified_on_all_attributes()
    {
        $base = new IdentityProviderAttributes(
            $this->getEntityAttributes(), true, true, new Keywords([])
        );
        $same = new IdentityProviderAttributes(
            $this->getEntityAttributes(), true, true, new Keywords([])
        );
        $differentAttributes = new IdentityProviderAttributes(
            new EntityAttributes(new LocalizedServiceName([]), new LocalizedDescription([]), new Logo('url', 1, 1)),
            true,
            true,
            new Keywords([])
        );
        $hidden = new IdentityProviderAttributes(
            $this->getEntityAttributes(),
            false,
            true,
            new Keywords([])
        );
        $disabledInWayf = new IdentityProviderAttributes(
            $this->getEntityAttributes(),
            true,
            false,
            new Keywords([])
        );
        $differentKeywords = new IdentityProviderAttributes(
            $this->getEntityAttributes(),
            true,
            false,
            new Keywords([new LocalizedKeywords('en', ['foo'])])
        );

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($differentAttributes), 'different attributes should not be equal');
        $this->assertFalse($base->equals($hidden), 'different hidden setting should not be equal');
        $this->assertFalse($base->equals($disabledInWayf), 'different wayf configuration should not be equal');
        $this->assertFalse($base->equals($differentKeywords), 'different keywords should not be equal');
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_serialized_identity_provider_visibility_settings_yields_an_equal_value_object()
    {
        $original = new IdentityProviderAttributes($this->getEntityAttributes(), true, true, new Keywords([]));

        $deserialized = IdentityProviderAttributes::deserialize($original->serialize());

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

        IdentityProviderAttributes::deserialize($notArray);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     * @dataProvider invalidDeserializationDataProvider
     *
     * @param array $invalidData
     */
    public function deserialization_requires_hidden_and_enabled_in_wayf_as_keys($invalidData)
    {
        $this->expectException(InvalidArgumentException::class);

        IdentityProviderAttributes::deserialize($invalidData);
    }

    public function invalidDeserializationDataProvider()
    {
        return [
            'no match' => [
                [
                    'foo'   => $this->getEntityAttributes()->serialize(),
                    'some'  => true,
                    'thing' => true,
                    'words' => []
                ]
            ],
            'no entity_attribyutes' => [
                [
                    'hidden'            => true,
                    'enbled_in_wayf'    => true,
                    'keywords'          => []
                ]
            ],
            'no hidden' => [
                [
                    'entity_attributes' => $this->getEntityAttributes()->serialize(),
                    'enbled_in_wayf'    => true,
                    'keywords'          => []
                ]
            ],
            'no enbled_in_wayf' => [
                [
                    'entity_attributes' => $this->getEntityAttributes()->serialize(),
                    'hidden'            => true,
                    'keywords'          => []
                ]
            ],
            'no keywords'       => [
                [
                    'entity_attributes' => $this->getEntityAttributes()->serialize(),
                    'hidden'            => true,
                    'enbled_in_wayf'    => true,
                ]
            ],
        ];
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function it_can_be_cast_to_string()
    {
        $original = new IdentityProviderAttributes($this->getEntityAttributes(), true, true, new Keywords([]));

        $this->assertInternalType('string', (string) $original);
    }

    private function getEntityAttributes()
    {
        return new EntityAttributes(
            new LocalizedServiceName([new LocalizedName('OpenConext', 'en')]),
            new LocalizedDescription([new LocalizedText('OpenConext', 'en')]),
            new Logo('https://domain.invalid/img.png', 150, 150)
        );
    }
}
