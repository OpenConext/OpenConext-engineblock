<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\Value\Saml\Metadata\Common\Binding;
use OpenConext\Value\Saml\Metadata\Common\Endpoint;
use PHPUnit_Framework_TestCase as UnitTest;
use stdClass;

class SingleSignOnServicesTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function all_services_must_be_an_endpoint()
    {
        $services = [
            new Endpoint(Binding::httpPost(), 'some:uri'),
            new Endpoint(Binding::httpPost(), 'other:uri'),
            new stdClass()
        ];

        $this->expectException(InvalidArgumentException::class);

        new SingleSignOnServices($services);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function an_endpoint_can_be_searched_for()
    {
        $searchingFor = new Endpoint(Binding::httpRedirect(), 'some:uri');
        $predicate = function (Endpoint $endpoint) use ($searchingFor) {
            return $endpoint->equals($searchingFor);
        };

        $singleSignOnService = new SingleSignOnServices([
            new Endpoint(Binding::httpRedirect(), 'some:uri'),
            new Endpoint(Binding::httpPost(), 'other:uri')
        ]);

        $this->assertTrue($searchingFor->equals($singleSignOnService->find($predicate)));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function find_returns_the_first_matching_endpoint()
    {
        $predicate = function (Endpoint $endpoint) {
            return $endpoint->getBinding()->isOfType(Binding::HTTP_POST);
        };

        $matchOne = new Endpoint(Binding::httpPost(), 'some:uri');
        $matchTwo = new Endpoint(Binding::httpPost(), 'some:other:uri');
        $notMatch = new Endpoint(Binding::httpRedirect(), 'uri');

        $singleSignOnService = new SingleSignOnServices([
            $notMatch,
            $matchOne,
            $matchTwo
        ]);

        $this->assertSame($matchOne, $singleSignOnService->find($predicate));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function find_returns_null_if_no_matching_element_is_found()
    {
        $predicate = function (Endpoint $endpoint) {
            return $endpoint->getBinding()->isOfType(Binding::HTTP_POST);
        };

        $notMatch = new Endpoint(Binding::httpRedirect(), 'uri');
        $alsoNotMatch = new Endpoint(Binding::httpArtifact(), 'some:uri');

        $singleSignOnService = new SingleSignOnServices([
            $notMatch,
            $alsoNotMatch
        ]);

        $this->assertNull($singleSignOnService->find($predicate));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function single_sign_on_services_are_only_equal_when_containing_the_same_elements_in_the_same_order()
    {
        $endpointOne   = new Endpoint(Binding::httpPost(), 'some:post:uri');
        $endpointTwo   = new Endpoint(Binding::httpArtifact(), 'some:artifact:uri');
        $endpointThree = new Endpoint(Binding::httpRedirect(), 'some:redirect:uri');

        $base           = new SingleSignOnServices([$endpointOne, $endpointTwo]);
        $same           = new SingleSignOnServices([$endpointOne, $endpointTwo]);
        $differentOrder = new SingleSignOnServices([$endpointTwo, $endpointOne]);
        $lessElements   = new SingleSignOnServices([$endpointOne]);
        $moreElements   = new SingleSignOnServices([$endpointOne, $endpointTwo, $endpointThree]);

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($differentOrder));
        $this->assertFalse($base->equals($lessElements));
        $this->assertFalse($base->equals($moreElements));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function single_sign_on_services_can_be_iterated_over()
    {
        $endpointOne = new Endpoint(Binding::httpPost(), 'some:post:uri');
        $endpointTwo = new Endpoint(Binding::httpArtifact(), 'some:artifact:uri');

        $singleSignOnServices = new SingleSignOnServices([$endpointOne, $endpointTwo]);

        $unknownElementSeen = false;
        $firstEndpointSeen = $secondEndpointSeen = false;

        foreach ($singleSignOnServices as $singleSignOnService) {
            if (!$firstEndpointSeen && $singleSignOnService === $endpointOne) {
                $firstEndpointSeen = true;
            } else if (!$secondEndpointSeen && $singleSignOnService === $endpointTwo) {
                $secondEndpointSeen = true;
            } else {
                $unknownElementSeen = true;
            }
        }

        $this->assertFalse($unknownElementSeen, 'Found unknown element while iterating over SingleSignOnServices');
        $this->assertTrue(
            $firstEndpointSeen,
            'Missing expected element emailOne when iterating over SingleSignOnServices'
        );
        $this->assertTrue(
            $secondEndpointSeen,
            'Missing expected element emailTwo when iterating over SingleSignOnServices'
        );
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_single_sign_on_service_elements_can_be_counted()
    {
        $endpointOne   = new Endpoint(Binding::httpPost(), 'some:post:uri');
        $endpointTwo   = new Endpoint(Binding::httpArtifact(), 'some:artifact:uri');
        $endpointThree = new Endpoint(Binding::httpRedirect(), 'some:redirect:uri');

        $twoElements = new SingleSignOnServices([$endpointOne, $endpointTwo]);
        $threeElements = new SingleSignOnServices([$endpointOne, $endpointTwo, $endpointThree]);

        $this->assertCount(2, $twoElements);
        $this->assertCount(3, $threeElements);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_single_sign_on_services_can_be_retrieved()
    {
        $endpointOne = new Endpoint(Binding::httpPost(), 'some:post:uri');
        $endpointTwo = new Endpoint(Binding::httpArtifact(), 'some:artifact:uri');

        $singleSignOnServices = new SingleSignOnServices([$endpointTwo, $endpointOne]);

        $this->assertSame(
            [$endpointTwo, $endpointOne],
            $singleSignOnServices->getEndpoints()
        );
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_serialized_single_sign_on_services_yields_an_equal_value_object()
    {
        $original = new SingleSignOnServices([
            new Endpoint(Binding::httpPost(), 'some:post:uri'),
            new Endpoint(Binding::httpArtifact(), 'some:artifact:uri')
        ]);

        $deserialized = SingleSignOnServices::deserialize($original->serialize());

        $this->assertTrue($original->equals($deserialized));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notArray
     */
    public function deserialization_requires_data_to_be_an_array($notArray)
    {
        $this->expectException(InvalidArgumentException::class);

        SingleSignOnServices::deserialize($notArray);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function single_sign_on_services_can_be_cast_to_string()
    {
        $services = new SingleSignOnServices([new Endpoint(Binding::httpRedirect(), 'some:uri')]);

        $this->assertInternalType('string', (string) $services);
    }
}
