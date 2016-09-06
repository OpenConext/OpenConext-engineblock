<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\Value\Saml\Metadata\Common\Binding;
use OpenConext\Value\Saml\Metadata\Common\Endpoint;
use OpenConext\Value\Saml\Metadata\Common\IndexedEndpoint;
use PHPUnit_Framework_TestCase as UnitTest;
use stdClass;

class AssertionConsumerServicesTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function all_elements_must_be_an_indexed_endpoint()
    {
        $invalidElements = [
            $this->getFirstIndexedEndpoint(),
            $this->getSecondIndexedEndpoint(),
            new stdClass()
        ];

        $this->expectException(InvalidArgumentException::class);
        new AssertionConsumerServices($invalidElements);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function presence_of_a_default_endpoint_can_be_queried()
    {
        $noDefault = new AssertionConsumerServices([
            $this->getFirstIndexedEndpoint(),
            $this->getSecondIndexedEndpoint()
        ]);
        $hasDefault = new AssertionConsumerServices([
            $this->getSecondIndexedEndpoint(),
            $this->getDefaultEndpoint()
        ]);

        $this->assertFalse($noDefault->hasDefaultEndpoint());
        $this->assertTrue($hasDefault->hasDefaultEndpoint());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function an_indexed_endpoint_can_be_searched_for()
    {
        $secondIndexedEndpoint = $this->getSecondIndexedEndpoint();
        $predicate = function (IndexedEndpoint $indexedEndpoint) use ($secondIndexedEndpoint) {
            return $indexedEndpoint->equals($secondIndexedEndpoint);
        };

        $firstIndexedEndpoint = $this->getFirstIndexedEndpoint();
        $secondIndexedEndpoint = $this->getSecondIndexedEndpoint();

        $list = new AssertionConsumerServices([$firstIndexedEndpoint, $secondIndexedEndpoint]);

        $this->assertSame($secondIndexedEndpoint, $list->find($predicate));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function find_returns_the_first_matching_element()
    {
        $secondIndexedEndpoint = $this->getSecondIndexedEndpoint();
        $predicate = function (IndexedEndpoint $indexedEndpoint) use ($secondIndexedEndpoint) {
            return $indexedEndpoint->equals($secondIndexedEndpoint);
        };

        $firstIndexedEndpoint  = $this->getFirstIndexedEndpoint();
        $secondIndexedEndpoint = $this->getSecondIndexedEndpoint();
        $notReturned           = $this->getSecondIndexedEndpoint();

        $list = new AssertionConsumerServices([$firstIndexedEndpoint, $secondIndexedEndpoint, $notReturned]);

        $this->assertSame($secondIndexedEndpoint, $list->find($predicate));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function null_is_returned_when_no_match_is_found()
    {
        $predicate = function () {
            return false;
        };

        $firstIndexedEndpoint  = $this->getFirstIndexedEndpoint();
        $secondIndexedEndpoint = $this->getSecondIndexedEndpoint();

        $list = new AssertionConsumerServices([$firstIndexedEndpoint, $secondIndexedEndpoint]);

        $this->assertNull($list->find($predicate));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function lists_are_only_equal_when_containing_the_same_elements_in_the_same_order()
    {
        $firstIndexedEndpoint  = $this->getFirstIndexedEndpoint();
        $secondIndexedEndpoint = $this->getSecondIndexedEndpoint();
        $thirdIndexedEndpoint  = $this->getDefaultEndpoint();

        $base           = new AssertionConsumerServices([$firstIndexedEndpoint, $secondIndexedEndpoint]);
        $theSame        = new AssertionConsumerServices([$firstIndexedEndpoint, $secondIndexedEndpoint]);
        $differentOrder = new AssertionConsumerServices([$secondIndexedEndpoint, $firstIndexedEndpoint]);
        $lessElements   = new AssertionConsumerServices([$firstIndexedEndpoint]);
        $moreElements   = new AssertionConsumerServices([
            $firstIndexedEndpoint,
            $secondIndexedEndpoint,
            $thirdIndexedEndpoint
        ]);

        $this->assertTrue($base->equals($theSame));
        $this->assertFalse($base->equals($differentOrder));
        $this->assertFalse($base->equals($lessElements));
        $this->assertFalse($base->equals($moreElements));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function an_indexed_endpoint_list_can_be_iterated_over()
    {
        $firstIndexedEndpoint = $this->getFirstIndexedEndpoint();
        $secondIndexedEndpoint = $this->getSecondIndexedEndpoint();

        $list = new AssertionConsumerServices([$firstIndexedEndpoint, $secondIndexedEndpoint]);

        $unknownElementSeen = false;
        $firstIndexedEndpointSeen      = $secondIndexedEndpointSeen = false;

        foreach ($list as $personAddress) {
            if (!$firstIndexedEndpointSeen && $personAddress === $firstIndexedEndpoint) {
                $firstIndexedEndpointSeen = true;
            } elseif (!$secondIndexedEndpointSeen && $personAddress === $secondIndexedEndpoint) {
                $secondIndexedEndpointSeen = true;
            } else {
                $unknownElementSeen = true;
            }
        }

        $this->assertFalse($unknownElementSeen, 'Found unknown element while iterating over AssertionConsumerServices');
        $this->assertTrue($firstIndexedEndpointSeen, 'Missing expected element emailOne when iterating over AssertionConsumerServices');
        $this->assertTrue($secondIndexedEndpointSeen, 'Missing expected element emailTwo when iterating over AssertionConsumerServices');
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_elements_in_an_indexed_endpoint_list_can_be_counted()
    {
        $numberOne   = $this->getFirstIndexedEndpoint();
        $numberTwo   = $this->getSecondIndexedEndpoint();
        $numberThree = $this->getDefaultEndpoint();

        $twoElements   = new AssertionConsumerServices([$numberThree, $numberTwo]);
        $threeElements = new AssertionConsumerServices([$numberThree, $numberTwo, $numberOne]);

        $this->assertCount(2, $twoElements);
        $this->assertCount(3, $threeElements);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_endpoints_can_be_retrieved_as_given()
    {
        $numberOne = $this->getFirstIndexedEndpoint();
        $numberTwo = $this->getSecondIndexedEndpoint();

        $list = new AssertionConsumerServices([$numberTwo, $numberOne, $numberTwo]);

        $this->assertSame(
            [$numberTwo, $numberOne, $numberTwo],
            $list->getEndpoints()
        );
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_a_serialized_contact_person_list_results_in_a_equal_value_object()
    {
        $firstIndexedEndpoint = $this->getFirstIndexedEndpoint();
        $secondIndexedEndpoint = $this->getSecondIndexedEndpoint();

        $original     = new AssertionConsumerServices([$firstIndexedEndpoint, $secondIndexedEndpoint]);
        $deserialized = AssertionConsumerServices::deserialize($original->serialize());

        $this->assertTrue($original->equals($deserialized));
    }

    /**
     * @test
     * @group        metadata
     * @group        contactperson
     *
     * @dataProvider \OpenConext\TestDataProvider::notArray
     * @expectedException InvalidArgumentException
     *
     * @param mixed $notArray
     */
    public function deserialization_requires_a_array($notArray)
    {
        AssertionConsumerServices::deserialize($notArray);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function an_indexed_endpoint_list_can_be_cast_to_string()
    {
        $numberOne = $this->getFirstIndexedEndpoint();
        $numberTwo = $this->getSecondIndexedEndpoint();

        $list = new AssertionConsumerServices([$numberOne, $numberTwo]);

        $this->assertTrue(is_string((string) $list));
    }

    /**
     * @return IndexedEndpoint
     */
    private function getFirstIndexedEndpoint()
    {
        return new IndexedEndpoint(
            new Endpoint(Binding::httpPost(), 'some:uri'),
            1,
            false
        );
    }

    /**
     * @return IndexedEndpoint
     */
    private function getSecondIndexedEndpoint()
    {
        return new IndexedEndpoint(
            new Endpoint(Binding::httpRedirect(), 'some:uri'),
            2,
            false
        );
    }

    /**
     * @return IndexedEndpoint
     */
    private function getDefaultEndpoint()
    {
        return new IndexedEndpoint(
            new Endpoint(Binding::httpPost(), 'some:uri'),
            0,
            true
        );
    }
}
