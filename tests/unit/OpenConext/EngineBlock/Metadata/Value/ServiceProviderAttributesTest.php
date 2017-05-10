<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlock\Metadata\Value\Common\LocalizedText;
use OpenConext\Value\Saml\Metadata\Common\LocalizedName;
use OpenConext\Value\Saml\Metadata\Common\LocalizedUri;
use PHPUnit_Framework_TestCase as UnitTest;

class ServiceProviderAttributesTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function service_name_can_be_retrieved()
    {
        $localizedServiceName      = new LocalizedServiceName([new LocalizedName('OpenConext', 'en')]);
        $serviceProviderAttributes = new ServiceProviderAttributes(
            new EntityAttributes(
                $localizedServiceName,
                new LocalizedDescription([new LocalizedText('OpenConext', 'en')]),
                new Logo('https://domain.invalid/img.png', 150, 150)
            ),
            new Url('http://domain.invalid/tos'),
            new LocalizedSupportUrl([])
        );

        $this->assertSame($localizedServiceName, $serviceProviderAttributes->getServiceName());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function description_can_be_retrieved()
    {
        $localizedDescription      = new LocalizedDescription([new LocalizedText('OpenConext', 'en')]);
        $serviceProviderAttributes = new ServiceProviderAttributes(
            new EntityAttributes(
                new LocalizedServiceName([new LocalizedName('OpenConext', 'en')]),
                $localizedDescription,
                new Logo('https://domain.invalid/img.png', 150, 150)
            ),
            new Url('http://domain.invalid/tos'),
            new LocalizedSupportUrl([])
        );

        $this->assertSame($localizedDescription, $serviceProviderAttributes->getDescription());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function logo_can_be_retrieved()
    {
        $logo                      = new Logo('https://domain.invalid/img.png', 150, 150);
        $serviceProviderAttributes = new ServiceProviderAttributes(
            new EntityAttributes(
                new LocalizedServiceName([new LocalizedName('OpenConext', 'en')]),
                new LocalizedDescription([new LocalizedText('OpenConext', 'en')]),
                $logo
            ),
            new Url('http://domain.invalid/tos'),
            new LocalizedSupportUrl([])
        );

        $this->assertSame($logo, $serviceProviderAttributes->getLogo());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function terms_of_service_url_can_be_retrieved()
    {
        $termsOfServiceUrl         = new Url('http://domain.invalid/tos');
        $serviceProviderAttributes = new ServiceProviderAttributes(
            $this->getEntityAttributes(),
            $termsOfServiceUrl,
            new LocalizedSupportUrl([])
        );

        $this->assertSame($termsOfServiceUrl, $serviceProviderAttributes->getTermsOfServiceUrl());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function support_url_can_be_retrieved()
    {
        $supportUrl                = new LocalizedSupportUrl([]);
        $serviceProviderAttributes = new ServiceProviderAttributes(
            $this->getEntityAttributes(),
            new Url('http://domain.invalid/tos'),
            $supportUrl
        );

        $this->assertSame($supportUrl, $serviceProviderAttributes->getSupportUrl());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function equality_is_verified_on_all_properties()
    {
        $base = new ServiceProviderAttributes(
            $this->getEntityAttributes(),
            new Url('http://domain.invalid/tos'),
            new LocalizedSupportUrl([])
        );
        $same = new ServiceProviderAttributes(
            $this->getEntityAttributes(),
            new Url('http://domain.invalid/tos'),
            new LocalizedSupportUrl([])
        );
        $differentEntityAttributes = new ServiceProviderAttributes(
            new EntityAttributes(
                new LocalizedServiceName([]),
                new LocalizedDescription([]),
                new Logo('https://domain.invalid/img.png', 150, 150)
            ),
            new Url('http://domain.invalid/tos'),
            new LocalizedSupportUrl([])
        );
        $differentTermsOfService = new ServiceProviderAttributes(
            $this->getEntityAttributes(),
            new Url('http://somewhere.invalid/t-o-s', 'nl'),
            new LocalizedSupportUrl([])
        );
        $differentSupportUrl = new ServiceProviderAttributes(
            $this->getEntityAttributes(),
            new Url('http://domain.invalid/tos'),
            new LocalizedSupportUrl([new LocalizedUri('http://domain.invalid/support', 'en')])
        );

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($differentEntityAttributes));
        $this->assertFalse($base->equals($differentTermsOfService));
        $this->assertFalse($base->equals($differentSupportUrl));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_serialized_service_provider_attributes_yields_an_equal_value_object()
    {
        $original = new ServiceProviderAttributes(
            $this->getEntityAttributes(),
            new Url('http://domain.invalid/tos'),
            new LocalizedSupportUrl([])
        );

        $deserialized = ServiceProviderAttributes::deserialize($original->serialize());

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

        ServiceProviderAttributes::deserialize($notArray);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     * @dataProvider invalidDataProvider
     *
     * @param array $invalidData
     */
    public function deserialization_enforces_presence_of_all_required_keys($invalidData)
    {
        $this->expectException(InvalidArgumentException::class);

        ServiceProviderAttributes::deserialize($invalidData);
    }

    /**
     * @return array
     */
    public function invalidDataProvider()
    {
        return [
            'no matches' => [
                [
                    'foo' => $this->getEntityAttributes()->serialize(),
                    'bar' => (new Url('https://en.domain.invalid/'))->serialize(),
                    'baz' => (new LocalizedSupportUrl([new LocalizedUri('uri', 'en')]))->serialize()
                ]
            ],
            'no entity_attributes' => [
                [
                    'terms_of_service_url' => (new Url('https://en.domain.invalid/'))->serialize(),
                    'support_urls'         => (new LocalizedSupportUrl([new LocalizedUri('uri', 'en')]))->serialize()
                ]
            ],
            'no terms_of_service_url' => [
                [
                    'entity_attributes'    => $this->getEntityAttributes()->serialize(),
                    'support_urls'         => (new LocalizedSupportUrl([new LocalizedUri('uri', 'en')]))->serialize()
                ]
            ],
            'no support_urls' => [
                [
                    'entity_attributes'    => $this->getEntityAttributes()->serialize(),
                    'terms_of_service_url' => (new Url('https://en.domain.invalid/'))->serialize(),
                ]
            ],
        ];
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function service_provider_attributes_can_be_cast_to_string()
    {
        $serviceProviderAttributes = new ServiceProviderAttributes(
            $this->getEntityAttributes(),
            new Url('http://domain.invalid/tos', 'en'),
            new LocalizedSupportUrl([])
        );

        $this->assertInternalType('string', (string) $serviceProviderAttributes);
    }

    /**
     * @return EntityAttributes
     */
    private function getEntityAttributes()
    {
        return new EntityAttributes(
            new LocalizedServiceName([new LocalizedName('OpenConext', 'en')]),
            new LocalizedDescription([new LocalizedText('OpenConext', 'en')]),
            new Logo('https://domain.invalid/img.png', 150, 150)
        );
    }
}
