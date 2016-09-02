<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use Mockery as m;
use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlock\Exception\LogicException;
use OpenConext\EngineBlock\Metadata\Value\AttributeReleasePolicy\AttributePolicy;
use PHPUnit_Framework_TestCase as UnitTest;
use stdClass;

class AttributeReleasePolicyTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::nonStringScalarOrEmptyString
     *
     * @param mixed $invalidKey
     */
    public function a_descriptor_can_only_have_non_empty_strings_as_keys($invalidKey)
    {
        $this->expectException(InvalidArgumentException::class);

        $descriptor = [$invalidKey => ['foo', 'bar']];

        AttributeReleasePolicy::fromDescriptor($descriptor);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     *
     * @dataProvider \OpenConext\TestDataProvider::notArray
     *
     * @param mixed $notArray
     */
    public function a_descriptor_can_only_have_arrays_as_values($notArray)
    {
        $this->expectException(InvalidArgumentException::class);

        $descriptor = ['key' => ['foo'], 'bar' => $notArray];

        AttributeReleasePolicy::fromDescriptor($descriptor);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function all_elements_must_be_an_attribute_policy()
    {
        $policyOne = new AttributePolicy('attribute:one', ['allowed']);
        $policyTwo = new AttributePolicy('attribute:two', [AttributePolicy::WILDCARD]);
        $stdClass  = new stdClass();

        $this->expectException(InvalidArgumentException::class);
        new AttributeReleasePolicy([$policyOne, $policyTwo, $stdClass]);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function presence_of_a_policy_for_an_attribute_can_be_tested()
    {
        $policyOne = new AttributePolicy('attribute:one', ['allowed']);
        $policyTwo = new AttributePolicy('attribute:two', [AttributePolicy::WILDCARD]);

        $policy = new AttributeReleasePolicy([$policyOne, $policyTwo]);

        $this->assertTrue($policy->hasPolicyFor('attribute:one'));
        $this->assertFalse($policy->hasPolicyFor('not:in:arp'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function only_the_first_policy_for_an_attribute_is_queried_whether_a_value_is_allowed()
    {
        $policyOne   = new AttributePolicy('some:attribute', ['allowed']);
        $policyTwo   = new AttributePolicy('the:attribute', ['not allowed :(']);
        $policyThree = new AttributePolicy('the:attribute', ['allowed']);

        $arp = new AttributeReleasePolicy([$policyOne, $policyTwo, $policyThree]);

        $this->assertFalse($arp->allowsValueFor('the:attribute', 'value'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function only_values_for_attributes_with_policies_can_be_tested()
    {
        $policyOne = new AttributePolicy('some:attribute', ['allowed']);
        $policyTwo = new AttributePolicy('other:attribute', ['value']);

        $arp = new AttributeReleasePolicy([$policyOne, $policyTwo]);

        $this->expectException(LogicException::class);
        $arp->allowsValueFor('attribute:without:policy', 'value');
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function the_attribute_policies_can_be_retrieved()
    {
        $policyOne = new AttributePolicy('some:attribute', ['allowed']);
        $policyTwo = new AttributePolicy('other:attribute', ['value']);

        $arp = new AttributeReleasePolicy([$policyOne, $policyTwo]);

        $this->assertSame([$policyOne, $policyTwo], $arp->getAttributePolicies());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function attribute_release_policies_are_equal_when_they_have_the_same_policies_in_the_same_order()
    {
        $policyOne   = new AttributePolicy('some:attribute', ['allowed']);
        $policyTwo   = new AttributePolicy('other:attribute', ['value']);
        $policyThree = new AttributePolicy('third:attribute', ['foobar']);

        $base              = new AttributeReleasePolicy([$policyOne, $policyTwo]);
        $same              = new AttributeReleasePolicy([$policyOne, $policyTwo]);
        $lessPolicies      = new AttributeReleasePolicy([$policyOne]);
        $morePolicies      = new AttributeReleasePolicy([$policyOne, $policyTwo, $policyThree]);
        $differentOrder    = new AttributeReleasePolicy([$policyTwo, $policyOne]);
        $differentPolicies = new AttributeReleasePolicy([$policyThree, $policyThree]);

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($lessPolicies));
        $this->assertFalse($base->equals($morePolicies));
        $this->assertFalse($base->equals($differentOrder));
        $this->assertFalse($base->equals($differentPolicies));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function deserializing_a_serialized_attribute_release_policy_yields_an_equal_value_object()
    {
        $policyOne = new AttributePolicy('some:attribute', ['allowed']);
        $policyTwo = new AttributePolicy('other:attribute', ['value']);

        $original     = new AttributeReleasePolicy([$policyOne, $policyTwo]);
        $deserialized = AttributeReleasePolicy::deserialize($original->serialize());

        $this->assertTrue($original->equals($deserialized));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Metadata
     */
    public function an_attribute_release_policy_can_be_cast_to_string()
    {
        $policyOne = new AttributePolicy('some:attribute', ['allowed']);
        $policyTwo = new AttributePolicy('other:attribute', ['value']);

        $policy = new AttributeReleasePolicy([$policyOne, $policyTwo]);

        $this->assertInternalType('string', (string) $policy);
    }
}
