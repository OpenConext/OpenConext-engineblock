<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlock\Metadata\Value\Common\LocalizedText;
use OpenConext\Value\Saml\Metadata\Common\LocalizedName;
use PHPUnit_Framework_TestCase as UnitTest;

class EntityAttributesTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function service_name_can_be_retrieved()
    {
        $serviceName = new LocalizedServiceName([new LocalizedName('OpenConext', 'en')]);

        $entityAttributes = new EntityAttributes(
            $serviceName,
            new LocalizedDescription([]),
            new Logo('https://domain.invalid/img.png', 150, 150)
        );

        $this->assertSame($serviceName, $entityAttributes->getServiceName());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function description_can_be_retrieved()
    {
        $description = new LocalizedDescription([new LocalizedText('OpenConext', 'en')]);

        $entityAttributes = new EntityAttributes(
            new LocalizedServiceName([]),
            $description,
            new Logo('https://domain.invalid/img.png', 150, 150)
        );

        $this->assertSame($description, $entityAttributes->getDescription());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function logo_can_be_retrieved()
    {
        $logo = new Logo('https://domain.invalid/img.png', 150, 150);

        $entityAttributes = new EntityAttributes(
            new LocalizedServiceName([]),
            new LocalizedDescription([]),
            $logo
        );

        $this->assertSame($logo, $entityAttributes->getLogo());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function equality_is_verified_on_all_properties()
    {
        $base = new EntityAttributes(
            new LocalizedServiceName([]),
            new LocalizedDescription([]),
            new Logo('https://domain.invalid/img.png', 150, 150)
        );
        $same = new EntityAttributes(
            new LocalizedServiceName([]),
            new LocalizedDescription([]),
            new Logo('https://domain.invalid/img.png', 150, 150)
        );
        $differentServiceName = new EntityAttributes(
            new LocalizedServiceName([new LocalizedName('OpenConext', 'en')]),
            new LocalizedDescription([]),
            new Logo('https://domain.invalid/img.png', 150, 150)
        );
        $differentDescription = new EntityAttributes(
            new LocalizedServiceName([]),
            new LocalizedDescription([new LocalizedText('OpenConext', 'en')]),
            new Logo('https://domain.invalid/img.png', 150, 150)
        );
        $differentLogo = new EntityAttributes(
            new LocalizedServiceName([]),
            new LocalizedDescription([]),
            new Logo('https://other.doamin.invalid/img.jpg', 150, 150)
        );

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($differentServiceName));
        $this->assertFalse($base->equals($differentDescription));
        $this->assertFalse($base->equals($differentLogo));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_serialized_entity_attributes_yields_an_equal_value_object()
    {
        $original = new EntityAttributes(
            new LocalizedServiceName([new LocalizedName('OpenConext', 'en')]),
            new LocalizedDescription([new LocalizedText('OpenConext', 'en')]),
            new Logo('https://domain.invalid/img.png', 150, 150)
        );

        $deserialized = EntityAttributes::deserialize($original->serialize());

        $this->assertTrue($original->equals($deserialized));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     * @dataProvider \OpenConext\TestDataProvider::notArray
     */
    public function deserialization_requires_data_to_be_an_array($notArray)
    {
        $this->expectException(InvalidArgumentException::class);

        EntityAttributes::deserialize($notArray);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     * @dataProvider invalidDataProvider
     */
    public function deserialization_enforces_the_presence_of_all_required_keys($invalidData)
    {
        $this->expectException(InvalidArgumentException::class);

        EntityAttributes::deserialize($invalidData);
    }

    public function invalidDataProvider()
    {
        return [
            'no matches' => [
                [
                    'foo' => (new LocalizedServiceName([]))->serialize(),
                    'bar' => (new LocalizedDescription([]))->serialize(),
                    'baz' => (new Logo('https://domain.invalid/img.png', 150, 150))->serialize()
                ]
            ],
            'no service_name' => [
                [
                    'description'  => (new LocalizedDescription([]))->serialize(),
                    'logo'         => (new Logo('https://domain.invalid/img.png', 150, 150))->serialize()
                ]
            ],
            'no description' => [
                [
                    'service_name' => (new LocalizedServiceName([]))->serialize(),
                    'logo'         => (new Logo('https://domain.invalid/img.png', 150, 150))->serialize()
                ]
            ],
            'no logo' => [
                [
                    'service_name' => (new LocalizedServiceName([]))->serialize(),
                    'description'  => (new LocalizedDescription([]))->serialize(),
                ]
            ],
        ];
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function entity_attributes_can_be_cast_to_string()
    {
        $entityAttributes = new EntityAttributes(
            new LocalizedServiceName([new LocalizedName('OpenConext', 'en')]),
            new LocalizedDescription([new LocalizedText('OpenConext', 'en')]),
            new Logo('https://domain.invalid/img.png', 150, 150)
        );

        $this->assertInternalType('string', (string) $entityAttributes);
    }
}
